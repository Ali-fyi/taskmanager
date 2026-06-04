<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Listeners\Concerns\InteractsWithQueuedListeners;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordTaskAssignment implements ShouldQueue
{
    use InteractsWithQueue;
    use InteractsWithQueuedListeners;

    public string $queue = 'notifications';

    public function handle(TaskAssigned $event): void
    {
        $task = Task::query()->find($event->task->id);

        if ($task === null) {
            Log::warning('Task assignment listener skipped: task no longer exists', [
                'task_id' => $event->task->id,
            ]);

            return;
        }

        if ($event->assignee === null) {
            Log::info('Task unassigned', [
                'task_id'  => $task->id,
                'actor_id' => $event->actor->id,
            ]);

            return;
        }

        Log::info('Task assigned (notification placeholder)', [
            'task_id'      => $task->id,
            'task_title'   => $task->title,
            'assignee_id'  => $event->assignee->id,
            'assignee_email' => $event->assignee->email,
            'actor_id'     => $event->actor->id,
        ]);

        // @todo Phase 2+: Mail::to($event->assignee)->send(new TaskAssignedMail($task, $event->actor));
    }
}
