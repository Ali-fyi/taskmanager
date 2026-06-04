<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    /**
     * Create a task and dispatch domain events.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(Project $project, array $attributes, User $actor): Task
    {
        $task = $project->tasks()->create($attributes);

        TaskCreated::dispatch($task, $actor);

        if ($task->assigned_to !== null) {
            $task->load('assignee');
            TaskAssigned::dispatch($task, $task->assignee, $actor);
        }

        return $task;
    }

    /**
     * Update a task and dispatch domain events when relevant fields change.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Task $task, array $attributes, User $actor): Task
    {
        $originalAssigneeId = $task->assigned_to;

        $changes = [];
        foreach ($attributes as $key => $value) {
            $current = $task->getAttribute($key);
            if ($current != $value) {
                $changes[$key] = ['old' => $current, 'new' => $value];
            }
        }

        if ($changes === []) {
            return $task;
        }

        $task->update($attributes);
        $task->refresh();

        TaskUpdated::dispatch($task, $actor, $changes);

        if (array_key_exists('assigned_to', $changes)) {
            $task->load('assignee');
            TaskAssigned::dispatch($task, $task->assignee, $actor);
        }

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
