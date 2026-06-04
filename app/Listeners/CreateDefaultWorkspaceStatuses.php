<?php

namespace App\Listeners;

use App\Events\WorkspaceCreated;

class CreateDefaultWorkspaceStatuses
{
    /**
     * Seed the default workflow statuses for a new workspace.
     *
     * @todo Phase 2: consider making this configurable per workspace template.
     */
    public function handle(WorkspaceCreated $event): void
    {
        $event->workspace->statuses()->createMany([
            ['name' => 'To do',       'color' => '#6366f1', 'position' => 1],
            ['name' => 'In progress', 'color' => '#f59e0b', 'position' => 2],
            ['name' => 'Done',        'color' => '#10b981', 'position' => 3],
        ]);
    }
}
