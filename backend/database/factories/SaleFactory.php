<?php

namespace Database\Factories;

use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SaleFactory extends Factory
{
    public function definition(): array
    {
        $membership = Membership::factory()->create();

        return ['business_id' => $membership->business_id, 'branch_id' => $membership->branch_id, 'customer_id' => null, 'created_by' => $membership->user_id, 'request_key' => (string) Str::uuid(), 'request_hash' => hash('sha256', 'fixture'), 'customer_name' => 'cliente de prueba', 'customer_phone' => null, 'business_name' => 'negocio de prueba', 'branch_name' => 'central', 'currency' => 'NIO', 'timezone' => 'America/Managua', 'total_minor' => 1995, 'notes' => null];
    }
}
