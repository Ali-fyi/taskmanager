<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use RuntimeException;

class WorkspaceMemberService
{
    /**
     * Ajoute un utilisateur existant à un workspace via son email.
     *
     * @throws RuntimeException Si l'utilisateur est déjà membre.
     */
    public function addMemberByEmail(Workspace $workspace, string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException(
                "{$user->name} est déjà membre de ce workspace."
            );
        }

        $workspace->members()->attach($user->id, ['role' => 'member']);

        return $user;
    }

    /**
     * Retire un membre du workspace.
     * L'owner ne peut pas se retirer lui-même.
     *
     * @throws RuntimeException Si le user est l'owner ou n'est pas membre.
     */
    public function removeMember(Workspace $workspace, User $user): void
    {
        if ($workspace->owner_id === $user->id) {
            throw new RuntimeException(
                'Le propriétaire ne peut pas être retiré du workspace.'
            );
        }

        if (! $workspace->members()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('Cet utilisateur n\'est pas membre de ce workspace.');
        }

        $workspace->members()->detach($user->id);
    }
}
