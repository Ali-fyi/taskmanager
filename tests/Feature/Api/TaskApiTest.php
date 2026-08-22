<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a workspace owned by the given user, with a project inside it.
     */
    private function projectFor(User $user): Project
    {
        $workspace = Workspace::factory()->ownedBy($user)->create();

        return Project::factory()->forWorkspace($workspace)->create();
    }

    public function test_index_only_returns_tasks_from_the_users_workspaces(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $mine    = Task::factory()->forProject($this->projectFor($user))->create();
        $foreign = Task::factory()->create(); // unrelated workspace

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id);

        $this->assertNotContains($foreign->id, collect($response->json('data'))->pluck('id'));
    }

    public function test_a_member_can_create_a_task(): void
    {
        $user    = User::factory()->create();
        Sanctum::actingAs($user);
        $project = $this->projectFor($user);
        $status  = Status::factory()->forWorkspace($project->workspace)->create();

        $response = $this->postJson('/api/tasks', [
            'project_id'  => $project->id,
            'title'       => 'API task',
            'status_id'   => $status->id,
            'assigned_to' => $user->id,
        ]);

        $response->assertCreated()->assertJsonPath('data.title', 'API task');
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title'      => 'API task',
        ]);
    }

    public function test_a_non_member_cannot_create_a_task_in_a_foreign_project(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $foreignProject = Project::factory()->create();

        $this->postJson('/api/tasks', [
            'project_id' => $foreignProject->id,
            'title'      => 'Intruder task',
        ])->assertForbidden();

        $this->assertDatabaseMissing('tasks', ['title' => 'Intruder task']);
    }

    public function test_creating_a_task_validates_input(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'title']);
    }

    public function test_a_member_can_view_a_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->forProject($this->projectFor($user))->create();

        $this->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_a_non_member_cannot_view_a_task(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $task = Task::factory()->create();

        $this->getJson("/api/tasks/{$task->id}")->assertForbidden();
    }

    public function test_a_member_can_update_a_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $project = $this->projectFor($user);
        $status  = Status::factory()->forWorkspace($project->workspace)->create();
        $task    = Task::factory()->forProject($project)->create(['title' => 'Before']);

        $this->putJson("/api/tasks/{$task->id}", [
            'title'     => 'After',
            'status_id' => $status->id,
        ])->assertOk()->assertJsonPath('data.title', 'After');

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'title'     => 'After',
            'status_id' => $status->id,
        ]);
    }

    public function test_a_member_can_delete_a_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->forProject($this->projectFor($user))->create();

        $this->deleteJson("/api/tasks/{$task->id}")->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_a_non_member_cannot_delete_a_task(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $task = Task::factory()->create();

        $this->deleteJson("/api/tasks/{$task->id}")->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
