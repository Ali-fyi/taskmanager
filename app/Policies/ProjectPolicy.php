<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * N'importe quel membre du workspace peut créer, voir et modifier des projets.
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
        return true; // la vérification workspace se fait dans le controller
    }

    public function update(User $user, Project $project): bool
    {
        return $this->isMember($user, $project);
    }

    /**
     * Seul le propriétaire du workspace peut supprimer un projet.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->workspace->owner_id === $user->id;
    }
}
