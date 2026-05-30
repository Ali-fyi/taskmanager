<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use RuntimeException;

class WorkspaceMemberService
{
    /**
     * Adds an existing user to a workspace via their email.
     *
     * @throws RuntimeException If the user is already a member.
     */
    public function addMemberByEmail(Workspace $workspace, string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($workspace->members()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException(
                "{$user->name} is already a member of this workspace."
            );
        }

        $workspace->members()->attach($user->id, ['role' => 'member']);

        return $user;
    }

    /**
     * Removes a member from the workspace.
     * The owner cannot remove themselves.
     *
     * @throws RuntimeException If the user is the owner or is not a member.
     */
    public function removeMember(Workspace $workspace, User $user): void
    {
        if ($workspace->owner_id === $user->id) {
            throw new RuntimeException(
                'The owner cannot be removed from the workspace.'
            );
        }

        if (! $workspace->members()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('This user is not a member of this workspace.');
        }

        $workspace->members()->detach($user->id);
    }
}
