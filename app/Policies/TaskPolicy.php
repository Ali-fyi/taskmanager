<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * N'importe quel membre du workspace parent peut voir et modifier les tâches.
     */
    private function isMember(User $user, Task $task): bool
    {
        return $task->project->workspace->members()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function view(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isMember($user, $task);
    }
}
