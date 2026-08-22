<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_only_returns_projects_from_the_users_workspaces(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $own     = Workspace::factory()->ownedBy($user)->create();
        $mine    = Project::factory()->forWorkspace($own)->create();
        $foreign = Project::factory()->create(); // unrelated workspace

        $response = $this->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);

        $this->assertNotContains($foreign->id, collect($response->json('data'))->pluck('id'));
    }

    public function test_a_member_can_create_a_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $workspace = Workspace::factory()->ownedBy($user)->create();

        $response = $this->postJson('/api/projects', [
            'workspace_id' => $workspace->id,
            'name'         => 'API Project',
            'description'  => 'Created over the API',
            'color'        => '#abcdef',
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'API Project');
        $this->assertDatabaseHas('projects', [
            'workspace_id' => $workspace->id,
            'name'         => 'API Project',
        ]);
    }

    public function test_a_non_member_cannot_create_a_project_in_a_foreign_workspace(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $foreign = Workspace::factory()->create();

        $this->postJson('/api/projects', [
            'workspace_id' => $foreign->id,
            'name'         => 'Intruder',
        ])->assertForbidden();

        $this->assertDatabaseMissing('projects', ['name' => 'Intruder']);
    }

    public function test_creating_a_project_validates_input(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/projects', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['workspace_id', 'name']);
    }

    public function test_a_member_can_view_a_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $workspace = Workspace::factory()->ownedBy($user)->create();
        $project   = Project::factory()->forWorkspace($workspace)->create();

        $this->getJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_a_non_member_cannot_view_a_project(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $project = Project::factory()->create();

        $this->getJson("/api/projects/{$project->id}")->assertForbidden();
    }

    public function test_a_member_can_update_a_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $workspace = Workspace::factory()->ownedBy($user)->create();
        $project   = Project::factory()->forWorkspace($workspace)->create();

        $this->putJson("/api/projects/{$project->id}", [
            'name'        => 'Renamed',
            'description' => 'Updated',
        ])->assertOk()->assertJsonPath('data.name', 'Renamed');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Renamed']);
    }

    public function test_only_the_owner_can_delete_a_project(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::factory()->ownedBy($owner)->create();
        $project   = Project::factory()->forWorkspace($workspace)->create();

        // A plain member is not allowed to delete.
        $member = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);
        Sanctum::actingAs($member);
        $this->deleteJson("/api/projects/{$project->id}")->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        // The owner can delete.
        Sanctum::actingAs($owner);
        $this->deleteJson("/api/projects/{$project->id}")->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
