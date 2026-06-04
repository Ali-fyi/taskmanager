<?php

namespace Tests\Feature\Listeners;

use App\Events\TaskAssigned;
use App\Events\WorkspaceMemberJoined;
use App\Listeners\LogWorkspaceMemberJoined;
use App\Listeners\RecordTaskAssignment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedListenersTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_member_joined_listener_is_queued(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $member = User::factory()->create();
        $workspace = Workspace::create([
            'name'        => 'Test Workspace',
            'description' => null,
            'owner_id'    => $owner->id,
        ]);

        WorkspaceMemberJoined::dispatch($workspace, $member, $owner);

        Queue::assertPushed(CallQueuedListener::class, function (CallQueuedListener $job) {
            return $job->class === LogWorkspaceMemberJoined::class;
        });
    }

    public function test_task_assigned_listener_is_queued(): void
    {
        Queue::fake();

        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $workspace = Workspace::create([
            'name'        => 'Test Workspace',
            'description' => null,
            'owner_id'    => $actor->id,
        ]);
        $project = $workspace->projects()->create([
            'name'  => 'Test Project',
            'color' => '#6366f1',
        ]);
        $task = $project->tasks()->create([
            'title'       => 'Test Task',
            'assigned_to' => $assignee->id,
        ]);

        TaskAssigned::dispatch($task, $assignee, $actor);

        Queue::assertPushed(CallQueuedListener::class, function (CallQueuedListener $job) {
            return $job->class === RecordTaskAssignment::class;
        });
    }
}
