<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;

class StatusPolicy
{
    /**
     * Only the workspace owner can manage statuses.
     */
    private function isOwner(User $user, Status $status): bool
    {
        return $status->workspace->owner_id === $user->id;
    }

    public function update(User $user, Status $status): bool
    {
        return $this->isOwner($user, $status);
    }

    public function delete(User $user, Status $status): bool
    {
        return $this->isOwner($user, $status);
    }
}
