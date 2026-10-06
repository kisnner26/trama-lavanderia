<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function roles(): array
    {
        return [
            'propietario' => [Role::Owner, true, true],
            'recepción' => [Role::Reception, true, false],
            'operación' => [Role::Operator, false, false],
        ];
    }

    #[DataProvider('roles')]
    public function test_each_role_has_only_its_assigned_abilities(Role $role, bool $receive, bool $manage): void
    {
        $membership = Membership::factory()->create(['role' => $role]);
        $gate = Gate::forUser($membership->user);

        $this->assertSame($receive, $gate->allows('receive', $membership));
        $this->assertSame($manage, $gate->allows('manage-catalog', $membership));
    }

    public function test_an_owner_membership_cannot_be_used_by_another_account(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('receive', $membership));
        $this->assertFalse(Gate::forUser($user)->allows('manage-catalog', $membership));
    }
}
