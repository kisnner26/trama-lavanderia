<?php

namespace Tests\Feature;

use App\Enums\BillingUnit;
use App\Enums\Role;
use App\Models\Business;
use App\Models\Membership;
use App\Models\Service;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function prices(): array
    {
        return [
            'sin costo' => ['0', 0, '0.00'],
            'entero' => ['1', 100, '1.00'],
            'un decimal' => ['0.1', 10, '0.10'],
            'dos decimales' => ['19.95', 1995, '19.95'],
            'cero inicial' => ['01.09', 109, '1.09'],
            'límite' => ['9999999.99', 999999999, '9999999.99'],
        ];
    }

    #[DataProvider('prices')]
    public function test_prices_are_saved_as_exact_minor_units(string $price, int $minorUnits, string $display): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        $foreignBusiness = Business::factory()->create();

        $this->actingAs($membership->user)->post(route('services.store', ['branch' => $membership->branch_id]), [
            'name' => 'lavado de prueba', 'billing_unit' => 'kg', 'price' => $price, 'requires_finish' => '0',
            'business_id' => $foreignBusiness->id, 'price_minor' => 1,
        ])->assertRedirectToRoute('services.index', ['branch' => $membership->branch_id]);

        $service = Service::sole();
        $this->assertSame($membership->business_id, $service->business_id);
        $this->assertSame($minorUnits, $service->price_minor);
        $this->assertSame($display, $service->formattedPrice());
        $this->assertSame(BillingUnit::Kilogram, $service->billing_unit);
        $this->assertFalse($service->requires_finish);
    }

    public function test_service_creation_requires_all_configuration_fields(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);

        $this->actingAs($membership->user)->post(route('services.store', ['branch' => $membership->branch_id]), [])
            ->assertSessionHasErrors([
                'name' => 'escribe el nombre del servicio.', 'billing_unit' => 'elige cómo se cobra el servicio.',
                'price' => 'escribe el precio del servicio.', 'requires_finish' => 'elige la ruta de trabajo.',
            ]);
        $this->assertDatabaseCount('services', 0);
    }

    public static function invalidConfiguration(): array
    {
        $priceMessage = 'usa un precio entre 0 y 9999999.99, con punto y hasta dos decimales.';

        return [
            'nombre largo' => ['name', str_repeat('x', 121), 'usa un nombre de hasta 120 caracteres.'],
            'nombre estructurado' => ['name', ['unexpected'], 'escribe un nombre válido.'],
            'unidad ajena' => ['billing_unit', 'litre', 'elige por pieza o por kilogramo.'],
            'ruta ajena' => ['requires_finish', 'maybe', 'elige una ruta con o sin acabado.'],
            'precio estructurado' => ['price', ['unexpected'], 'escribe el precio como un número decimal.'],
            'precio negativo' => ['price', '-1.00', $priceMessage],
            'coma decimal' => ['price', '1,00', $priceMessage],
            'notación científica' => ['price', '1e2', $priceMessage],
            'precisión excesiva' => ['price', '1.001', $priceMessage],
            'precio fuera del límite' => ['price', '10000000.00', $priceMessage],
        ];
    }

    #[DataProvider('invalidConfiguration')]
    public function test_invalid_configuration_is_rejected(string $field, mixed $value, string $message): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        $payload = ['name' => 'lavado de prueba', 'billing_unit' => 'piece', 'price' => '19.95', 'requires_finish' => '1'];
        $payload[$field] = $value;

        $this->actingAs($membership->user)->post(route('services.store', ['branch' => $membership->branch_id]), $payload)
            ->assertSessionHasErrors([$field => $message]);
        $this->assertDatabaseCount('services', 0);
    }

    #[TestWith([Role::Reception])]
    #[TestWith([Role::Operator])]
    public function test_only_an_owner_can_configure_prices(Role $role): void
    {
        $membership = Membership::factory()->create(['role' => $role]);

        $this->actingAs($membership->user)->post(route('services.store', ['branch' => $membership->branch_id]), [
            'name' => 'lavado de prueba', 'billing_unit' => 'piece', 'price' => '19.95', 'requires_finish' => '1',
        ])->assertForbidden();
        $this->assertDatabaseCount('services', 0);
    }

    public function test_reception_can_consult_the_catalogue_without_a_configuration_form(): void
    {
        $membership = Membership::factory()->create();
        Service::factory()->create(['business_id' => $membership->business_id, 'name' => 'servicio propio']);
        Service::factory()->create(['name' => 'servicio ajeno']);

        $this->actingAs($membership->user)->get(route('services.index', ['branch' => $membership->branch_id]))
            ->assertOk()->assertSee('servicio propio')->assertSee('19.95')->assertSee('por pieza')
            ->assertDontSee('servicio ajeno')->assertDontSee('guardar servicio');
    }

    public function test_operator_cannot_consult_prices(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Operator]);

        $this->actingAs($membership->user)->get(route('services.index', ['branch' => $membership->branch_id]))->assertForbidden();
    }

    public function test_an_owner_cannot_configure_another_business_catalogue(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        $other = Membership::factory()->create();

        $this->actingAs($membership->user)->post(route('services.store', ['branch' => $other->branch_id]), [
            'name' => 'lavado de prueba', 'billing_unit' => 'piece', 'price' => '19.95', 'requires_finish' => '1',
        ])->assertNotFound();
        $this->assertDatabaseCount('services', 0);
    }

    public function test_service_names_are_escaped(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        Service::factory()->create(['business_id' => $membership->business_id, 'name' => '<script>service()</script>']);

        $this->actingAs($membership->user)->get(route('services.index', ['branch' => $membership->branch_id]))
            ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>service()', false);
    }
}
