<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Membership;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_reception_saves_a_customer_in_its_own_business_only(): void
    {
        $membership = Membership::factory()->create();
        $foreignBusiness = Business::factory()->create();

        $this->actingAs($membership->user)->post(route('customers.store', ['branch' => $membership->branch_id]), [
            'name' => 'cliente de prueba', 'phone' => '+505 8000 0000', 'notes' => 'solicita comprobante',
            'business_id' => $foreignBusiness->id, 'id' => 800, 'role' => 'owner',
        ])->assertRedirectToRoute('customers.index', ['branch' => $membership->branch_id]);

        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseHas('customers', ['business_id' => $membership->business_id, 'name' => 'cliente de prueba', 'notes' => 'solicita comprobante']);
        $this->assertDatabaseMissing('customers', ['business_id' => $foreignBusiness->id]);
        $this->assertDatabaseMissing('customers', ['id' => 800]);
    }

    public function test_operator_cannot_read_customers(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Operator]);

        $this->actingAs($membership->user)->get(route('customers.index', ['branch' => $membership->branch_id]))->assertForbidden()->assertSee('esa tarea requiere otro permiso.');
    }

    public function test_operator_cannot_create_a_customer(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Operator]);

        $this->actingAs($membership->user)->post(route('customers.store', ['branch' => $membership->branch_id]), ['name' => 'cliente de prueba'])->assertForbidden();
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_search_never_returns_another_business_customer(): void
    {
        $membership = Membership::factory()->create();
        Customer::factory()->create(['business_id' => $membership->business_id, 'name' => 'cliente visible', 'phone' => '+505 8111 1111']);
        Customer::factory()->create(['name' => 'cliente ajeno', 'phone' => '+505 8111 1111']);

        $this->actingAs($membership->user)->get(route('customers.index', ['branch' => $membership->branch_id, 'q' => '8111']))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertSee('cliente visible')->assertDontSee('cliente ajeno')->assertViewHas('customers', fn ($records): bool => $records->total() === 1);
    }

    public function test_a_foreign_branch_cannot_be_used_to_create_a_customer(): void
    {
        $membership = Membership::factory()->create();
        $foreignMembership = Membership::factory()->create();

        $this->actingAs($membership->user)->post(route('customers.store', ['branch' => $foreignMembership->branch_id]), ['name' => 'cliente de prueba'])->assertNotFound();
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_invalid_customer_data_is_rejected_without_a_partial_record(): void
    {
        $membership = Membership::factory()->create();

        $this->actingAs($membership->user)->post(route('customers.store', ['branch' => $membership->branch_id]), ['phone' => 'abc', 'notes' => str_repeat('x', 1001)])
            ->assertSessionHasErrors([
                'name' => 'escribe el nombre del cliente.',
                'phone' => 'usa un teléfono de 7 a 30 caracteres, con números, espacios o guiones.',
                'notes' => 'usa una nota de hasta 1000 caracteres.',
            ]);
        $this->assertDatabaseCount('customers', 0);
    }

    public static function invalidCustomerFields(): array
    {
        return [
            'nombre largo' => ['name', str_repeat('x', 121), 'usa un nombre de hasta 120 caracteres.'],
            'nombre estructurado' => ['name', ['unexpected'], 'escribe un nombre válido.'],
            'teléfono largo' => ['phone', str_repeat('8', 31), 'usa un teléfono de hasta 30 caracteres.'],
            'teléfono estructurado' => ['phone', ['unexpected'], 'escribe un teléfono válido.'],
            'nota estructurada' => ['notes', ['unexpected'], 'escribe una nota válida.'],
        ];
    }

    #[DataProvider('invalidCustomerFields')]
    public function test_invalid_field_types_and_lengths_are_rejected(string $field, mixed $value, string $message): void
    {
        $membership = Membership::factory()->create();
        $payload = ['name' => 'cliente de prueba', $field => $value];

        $this->actingAs($membership->user)->post(route('customers.store', ['branch' => $membership->branch_id]), $payload)
            ->assertSessionHasErrors([$field => $message]);
        $this->assertDatabaseCount('customers', 0);
    }

    public function test_search_length_is_limited(): void
    {
        $membership = Membership::factory()->create();

        $this->actingAs($membership->user)->get(route('customers.index', ['branch' => $membership->branch_id, 'q' => str_repeat('x', 81)]))
            ->assertSessionHasErrors(['q' => 'busca con hasta 80 caracteres.']);
    }

    public function test_customer_text_is_escaped(): void
    {
        $membership = Membership::factory()->create();
        Customer::factory()->create(['business_id' => $membership->business_id, 'name' => '<script>name()</script>', 'phone' => '<script>phone()</script>', 'notes' => '<script>notes()</script>']);

        $this->actingAs($membership->user)->get(route('customers.index', ['branch' => $membership->branch_id]))
            ->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>name()', false)->assertDontSee('<script>phone()', false)->assertDontSee('<script>notes()', false);
    }

    public function test_the_customer_registry_is_paginated(): void
    {
        $membership = Membership::factory()->create();
        Customer::factory()->count(16)->create(['business_id' => $membership->business_id]);

        $this->actingAs($membership->user)->get(route('customers.index', ['branch' => $membership->branch_id]))
            ->assertOk()->assertViewHas('customers', fn ($records): bool => $records->total() === 16 && $records->count() === 15);
    }
}
