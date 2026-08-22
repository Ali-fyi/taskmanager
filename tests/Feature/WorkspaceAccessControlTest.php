<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $workspace = Workspace::factory()->create();

        $this->get(route('workspaces.show', $workspace))->assertRedirect('/login');
    }

    public function test_index_only_lists_workspaces_the_user_belongs_to(): void
    {
        $user = User::factory()->create();
        $own  = Workspace::factory()->ownedBy($user)->create();
        $foreign = Workspace::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk()
            ->assertSee($own->name)
            ->assertDontSee($foreign->name);
    }

    public function test_a_member_can_view_a_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $member    = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->get(route('workspaces.show', $workspace))
            ->assertOk()
            ->assertSee($workspace->name);
    }

    public function test_a_non_member_cannot_view_a_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $outsider  = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('workspaces.show', $workspace))
            ->assertForbidden();
    }

    public function test_only_the_owner_can_edit_a_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $member    = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);

        // A plain member may view but not edit.
        $this->actingAs($member)
            ->get(route('workspaces.edit', $workspace))
            ->assertForbidden();

        $this->actingAs($workspace->owner)
            ->get(route('workspaces.edit', $workspace))
            ->assertOk();
    }

    public function test_a_member_cannot_update_a_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $member    = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->put(route('workspaces.update', $workspace), [
                'name'        => 'Hijacked',
                'description' => 'nope',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('workspaces', ['name' => 'Hijacked']);
    }

    public function test_only_the_owner_can_delete_a_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $member    = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertForbidden();
        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);

        $this->actingAs($workspace->owner)
            ->delete(route('workspaces.destroy', $workspace))
            ->assertRedirect(route('workspaces.index'));
        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
    }
}
