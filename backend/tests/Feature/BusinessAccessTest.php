<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BusinessAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_membership_keeps_its_branch_and_role(): void
    {
        $membership = Membership::factory()->create(['role' => Role::Owner]);

        $this->assertSame($membership->business_id, $membership->branch->business_id);
        $this->assertSame(Role::Owner, $membership->fresh()->role);
        $this->assertTrue($membership->user->memberships->contains($membership));
    }

    public function test_a_membership_cannot_point_to_another_business_branch(): void
    {
        $branch = Branch::factory()->create();
        $business = Business::factory()->create();

        $this->expectException(QueryException::class);
        Membership::factory()->create(['branch_id' => $branch->id, 'business_id' => $business->id]);
    }

    public function test_a_user_cannot_have_conflicting_roles_in_the_same_branch(): void
    {
        $membership = Membership::factory()->create();

        $this->expectException(QueryException::class);
        Membership::factory()->create([
            'user_id' => $membership->user_id,
            'branch_id' => $membership->branch_id,
            'business_id' => $membership->business_id,
            'role' => Role::Owner,
        ]);
    }
}
