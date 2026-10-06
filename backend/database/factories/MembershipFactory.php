<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MembershipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'business_id' => fn (array $attributes): int => Branch::findOrFail($attributes['branch_id'])->business_id,
            'user_id' => User::factory(),
            'role' => Role::Reception,
        ];
    }
}
