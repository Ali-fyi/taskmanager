<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Listeners\Concerns\InteractsWithQueuedListeners;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogTaskCreated implements ShouldQueue
{
    use InteractsWithQueue;
    use InteractsWithQueuedListeners;

    public string $queue = 'logs';

    public function handle(TaskCreated $event): void
    {
        $task = Task::query()->find($event->task->id);

        if ($task === null) {
            return;
        }

        Log::info('Task created', [
            'task_id'    => $task->id,
            'project_id' => $task->project_id,
            'actor_id'   => $event->actor->id,
            'title'      => $task->title,
        ]);
    }
}
