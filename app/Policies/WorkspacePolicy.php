<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * N'importe quel utilisateur authentifié peut créer un workspace.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Seuls les membres du workspace peuvent le consulter.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Seul le propriétaire peut inviter / retirer des membres.
     */
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }

    /**
     * Seul le propriétaire peut modifier le workspace.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }

    /**
     * Seul le propriétaire peut supprimer le workspace.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }
}
