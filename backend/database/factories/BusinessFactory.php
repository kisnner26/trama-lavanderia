<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->company(), 'currency' => 'NIO', 'timezone' => 'America/Managua'];
    }
}
