<?php

namespace App\Listeners;

use App\Events\WorkspaceMemberJoined;
use App\Listeners\Concerns\InteractsWithQueuedListeners;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogWorkspaceMemberJoined implements ShouldQueue
{
    use InteractsWithQueue;
    use InteractsWithQueuedListeners;

    /**
     * Dedicated queue for member / invitation side effects.
     */
    public string $queue = 'notifications';

    public function handle(WorkspaceMemberJoined $event): void
    {
        Log::info('Workspace member joined', [
            'workspace_id'   => $event->workspace->id,
            'workspace_name' => $event->workspace->name,
            'member_id'      => $event->member->id,
            'member_email'   => $event->member->email,
            'invited_by'     => $event->invitedBy->id,
        ]);

        // @todo Phase 2+: Mail::to($event->member)->send(new WorkspaceInvitationMail(...));
    }
}
