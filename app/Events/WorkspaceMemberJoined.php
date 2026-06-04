<?php

namespace App\Events;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceMemberJoined
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Workspace $workspace,
        public User $member,
        public User $invitedBy,
    ) {}
}
