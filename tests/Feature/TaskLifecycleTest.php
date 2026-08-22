<?php

namespace Tests\Feature;

use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Workspace $workspace;
    private Project $project;
    private Status $todo;
    private Status $done;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner     = User::factory()->create();
        $this->workspace = Workspace::factory()->ownedBy($this->owner)->create();
        $this->project   = Project::factory()->forWorkspace($this->workspace)->create();
        $this->todo      = Status::factory()->forWorkspace($this->workspace)->create(['name' => 'To Do', 'position' => 0]);
        $this->done      = Status::factory()->forWorkspace($this->workspace)->create(['name' => 'Done', 'position' => 1]);
    }

    public function test_a_member_can_create_a_task_and_a_creation_event_is_dispatched(): void
    {
        Event::fake([TaskCreated::class, TaskAssigned::class]);

        $response = $this->actingAs($this->owner)->post(
            route('workspaces.projects.tasks.store', [$this->workspace, $this->project]),
            [
                'title'       => 'Write the tests',
                'description' => 'Cover the task lifecycle',
                'status_id'   => $this->todo->id,
                'assigned_to' => $this->owner->id,
                'due_date'    => now()->addWeek()->toDateString(),
            ],
        );

        $response->assertRedirect(route('workspaces.projects.show', [$this->workspace, $this->project]));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'title'      => 'Write the tests',
            'status_id'  => $this->todo->id,
            'assigned_to' => $this->owner->id,
        ]);

        Event::assertDispatched(TaskCreated::class);
        Event::assertDispatched(TaskAssigned::class);
    }

    public function test_creating_a_task_requires_a_title(): void
    {
        $this->actingAs($this->owner)
            ->post(route('workspaces.projects.tasks.store', [$this->workspace, $this->project]), [
                'title' => '',
            ])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_a_member_can_move_a_task_to_another_status(): void
    {
        Event::fake([TaskUpdated::class]);

        $task = Task::factory()->forProject($this->project)->create([
            'status_id' => $this->todo->id,
            'title'     => 'Movable task',
        ]);

        $this->actingAs($this->owner)
            ->put(route('tasks.update', $task), [
                'title'     => 'Movable task',
                'status_id' => $this->done->id,
            ])
            ->assertRedirect(route('workspaces.projects.show', [$this->workspace, $this->project]));

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'status_id' => $this->done->id,
        ]);

        Event::assertDispatched(TaskUpdated::class);
    }

    public function test_a_member_can_delete_a_task(): void
    {
        $task = Task::factory()->forProject($this->project)->create();

        $this->actingAs($this->owner)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('workspaces.projects.show', [$this->workspace, $this->project]));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_a_non_member_cannot_view_a_task(): void
    {
        $task     = Task::factory()->forProject($this->project)->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    public function test_a_non_member_cannot_update_a_task(): void
    {
        $task     = Task::factory()->forProject($this->project)->create(['title' => 'Original']);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->put(route('tasks.update', $task), ['title' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Original']);
    }

    public function test_a_non_member_cannot_delete_a_task(): void
    {
        $task     = Task::factory()->forProject($this->project)->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
