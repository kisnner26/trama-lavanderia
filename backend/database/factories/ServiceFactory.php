<?php

namespace Database\Factories;

use App\Enums\BillingUnit;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(), 'name' => fake()->words(3, true),
            'billing_unit' => BillingUnit::Piece, 'price_minor' => 1995, 'requires_finish' => true,
        ];
    }
}
