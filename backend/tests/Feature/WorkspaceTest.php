<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Membership;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_a_guest_is_redirected_to_login(): void
    {
        $this->get('/sucursales/1')->assertRedirectToRoute('login');
    }

    public function test_an_assigned_user_sees_their_own_branch(): void
    {
        $membership = Membership::factory()->create();

        $this->actingAs($membership->user)->get(route('workspace', ['branch' => $membership->branch_id]))
            ->assertOk()->assertSee($membership->branch->name)->assertSee($membership->role->label());
    }

    public function test_another_business_branch_is_not_disclosed(): void
    {
        $membership = Membership::factory()->create();
        $otherBranch = Branch::factory()->create();

        $this->actingAs($membership->user)->get(route('workspace', ['branch' => $otherBranch->id]))->assertNotFound();
    }

    public function test_a_sibling_branch_also_requires_an_explicit_assignment(): void
    {
        $membership = Membership::factory()->create();
        $sibling = Branch::factory()->create(['business_id' => $membership->business_id]);

        $this->actingAs($membership->user)->get(route('workspace', ['branch' => $sibling->id]))->assertNotFound();
    }

    public function test_a_revoked_membership_loses_access_on_the_next_request(): void
    {
        $membership = Membership::factory()->create();
        $user = $membership->user;
        $membership->delete();

        $this->actingAs($user)->get(route('workspace', ['branch' => $membership->branch_id]))->assertNotFound();
    }

    public function test_names_are_escaped_in_the_workspace(): void
    {
        $membership = Membership::factory()->create();
        $membership->branch->update(['name' => '<script>branch()</script>']);
        $membership->branch->business->update(['name' => '<script>business()</script>']);
        $membership->user->update(['name' => '<script>user()</script>']);

        $this->actingAs($membership->user)->get(route('workspace', ['branch' => $membership->branch_id]))
            ->assertOk()->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>branch()', false)->assertDontSee('<script>business()', false)->assertDontSee('<script>user()', false);
    }
}
