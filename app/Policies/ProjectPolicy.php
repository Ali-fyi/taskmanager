<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Any workspace member can create, view and edit projects.
     */
    private function isMember(User $user, Project $project): bool
    {
        return $project->workspace->members()->where("user_id", $user->id)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    public function create(User $user): bool
    {
        return true; // the workspace check is done in the controller
    }

    public function update(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    /**
     * Only the workspace owner can delete a project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->workspace->owner_id === $user->id;
    }
}
