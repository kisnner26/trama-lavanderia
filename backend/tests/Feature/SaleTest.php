<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Membership;
use App\Models\Sale;
use App\Models\Service;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function fixture(): array
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        $customer = Customer::factory()->create(['business_id' => $membership->business_id]);
        $service = Service::factory()->create(['business_id' => $membership->business_id, 'price_minor' => 1995]);
        $payload = ['request_key' => (string) Str::uuid(), 'customer_id' => $customer->id, 'lines' => [['service_id' => $service->id, 'quantity' => '2']]];
        $this->actingAs($membership->user);

        return [$membership, $customer, $service, $payload];
    }

    private function issue(Membership $membership, array $payload): Sale
    {
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertSessionHasNoErrors()->assertRedirect();

        return Sale::latest('id')->firstOrFail();
    }

    public function test_sales_snapshot_prices_and_retry_without_duplicate_records(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $sale = $this->issue($membership, $payload);
        $this->assertSame(3990, $sale->total_minor);
        $this->assertSame(0, $sale->paidMinor());
        $this->assertDatabaseCount('sale_events', 1);
        $service->update(['price_minor' => 9900, 'name' => 'nuevo precio']);
        $customer->update(['name' => 'nombre nuevo']);
        $this->issue($membership, $payload);
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_lines', 1);
        $this->assertDatabaseCount('sale_events', 1);
        $this->get(route('sales.receipt', ['branch' => $membership->branch_id, 'sale' => $sale->id]))->assertOk()->assertSee('39.90')->assertDontSee('nuevo precio')->assertDontSee('nombre nuevo');
        $payload['lines'][0]['quantity'] = '3';
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertConflict();
    }

    public function test_kilogram_rounding_is_exact_and_client_prices_are_ignored(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $service->update(['billing_unit' => 'kg', 'price_minor' => 1995]);
        $payload['lines'][0]['quantity'] = '0.100';
        $payload['lines'][0]['price_minor'] = 1;
        $payload['total_minor'] = 1;
        $sale = $this->issue($membership, $payload);
        $this->assertSame(200, $sale->total_minor);
        $this->assertSame($membership->business_id, $sale->business_id);
    }

    #[TestWith(['0'])]
    #[TestWith(['1.5'])]
    #[TestWith(['1e3'])]
    #[TestWith(['1,5'])]
    #[TestWith(['10000'])]
    #[TestWith([['unexpected']])]
    public function test_invalid_piece_quantities_do_not_create_partial_sales(mixed $quantity): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $payload['lines'][0]['quantity'] = $quantity;
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertSessionHasErrors('lines.0.quantity');
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_lines', 0);
        $this->assertDatabaseCount('sale_events', 0);
    }

    public function test_foreign_customer_and_service_are_rejected(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $foreign = Customer::factory()->create();
        $payload['customer_id'] = $foreign->id;
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertSessionHasErrors('customer_id');
        $payload['customer_id'] = $customer->id;
        $payload['lines'][] = ['service_id' => Service::factory()->create()->id, 'quantity' => '1'];
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertSessionHasErrors('lines.1.service_id');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_partial_payments_are_idempotent_and_cannot_exceed_the_balance(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $sale = $this->issue($membership, $payload);
        $url = route('sales.payment', ['branch' => $membership->branch_id, 'sale' => $sale->id]);
        $payment = ['request_key' => (string) Str::uuid(), 'amount' => '10.10', 'method' => 'cash'];
        $this->post($url, $payment)->assertSessionHasNoErrors()->assertRedirect();
        $this->post($url, $payment)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('sale_events', 2);
        $this->assertSame(1010, $sale->fresh()->paidMinor());
        $this->post($url, array_replace($payment, ['amount' => '10.11']))->assertConflict();
        foreach (['0', '29.81', '-1', '1.001'] as $amount) {
            $this->post($url, array_replace($payment, ['request_key' => (string) Str::uuid(), 'amount' => $amount]))->assertSessionHasErrors('amount');
        }
        $this->post($url, array_replace($payment, ['request_key' => (string) Str::uuid(), 'amount' => '29.80']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(3990, $sale->fresh()->paidMinor());
        $this->get(route('sales.show', ['branch' => $membership->branch_id, 'sale' => $sale->id]))->assertOk()->assertSee('venta pagada.');
    }

    public function test_receipts_are_read_only_and_preparation_is_recorded_separately(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $customer->update(['name' => '<script>customer()</script>']);
        $payload['notes'] = '<script>notes()</script>';
        $sale = $this->issue($membership, $payload);
        $route = ['branch' => $membership->branch_id, 'sale' => $sale->id];
        $this->get(route('sales.receipt', $route))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>customer()', false)->assertSee('no es factura fiscal');
        $this->assertDatabaseCount('sale_events', 1);
        $this->post(route('sales.prepare-receipt', $route))->assertRedirectToRoute('sales.receipt', $route);
        $this->assertDatabaseCount('sale_events', 2);
        $this->get(route('sales.index', ['branch' => $membership->branch_id, 'q' => $sale->number()]))->assertOk()->assertSee($sale->number());
    }

    public function test_same_business_sibling_branch_cannot_access_or_pay_a_sale(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $sale = $this->issue($membership, $payload);
        $branch = Branch::factory()->create(['business_id' => $membership->business_id]);
        Membership::factory()->create(['business_id' => $membership->business_id, 'branch_id' => $branch->id, 'user_id' => $membership->user_id, 'role' => Role::Owner]);
        $route = ['branch' => $branch->id, 'sale' => $sale->id];
        $this->get(route('sales.show', $route))->assertNotFound();
        $this->get(route('sales.receipt', $route))->assertNotFound();
        $this->post(route('sales.prepare-receipt', $route))->assertNotFound();
        $this->post(route('sales.payment', $route), ['request_key' => (string) Str::uuid(), 'amount' => '1', 'method' => 'cash'])->assertNotFound();
        $this->get(route('sales.index', ['branch' => $branch->id]))->assertOk()->assertDontSee($sale->number());
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('sale_events', 1);
    }

    public function test_malformed_lines_return_validation_errors_and_a_usable_form(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $payload['lines'] = ['invalid'];
        $payload['customer_id'] = ['invalid'];
        $payload['notes'] = ['invalid'];
        $payload['request_key'] = ['invalid'];
        $url = route('sales.create', ['branch' => $membership->branch_id]);
        $this->from($url)->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertSessionHasErrors('lines.0');
        $this->get($url)->assertOk()->assertSee('emitir comprobante');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_operator_and_other_branches_cannot_access_sale_documents(): void
    {
        [$membership, $customer, $service, $payload] = $this->fixture();
        $sale = $this->issue($membership, $payload);
        $other = Membership::factory()->create();
        foreach (['sales.show', 'sales.receipt'] as $name) {
            $this->actingAs($other->user)->get(route($name, ['branch' => $other->branch_id, 'sale' => $sale->id]))->assertNotFound();
        }
        $membership->update(['role' => Role::Operator]);
        $this->actingAs($membership->user)->get(route('sales.index', ['branch' => $membership->branch_id]))->assertForbidden();
        $this->post(route('sales.store', ['branch' => $membership->branch_id]), $payload)->assertForbidden();
        $this->post(route('sales.prepare-receipt', ['branch' => $membership->branch_id, 'sale' => $sale->id]))->assertForbidden();
    }
}
