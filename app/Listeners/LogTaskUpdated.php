<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Listeners\Concerns\InteractsWithQueuedListeners;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogTaskUpdated implements ShouldQueue
{
    use InteractsWithQueue;
    use InteractsWithQueuedListeners;

    public string $queue = 'logs';

    public function handle(TaskUpdated $event): void
    {
        $task = Task::query()->find($event->task->id);

        if ($task === null) {
            return;
        }

        Log::info('Task updated', [
            'task_id'  => $task->id,
            'actor_id' => $event->actor->id,
            'changes'  => array_keys($event->changes),
        ]);
    }
}
