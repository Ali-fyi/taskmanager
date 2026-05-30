<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStatusRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Status;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StatusController extends Controller
{
    /**
     * Lists the workspace's statuses.
     * Only members can see the list.
     */
    public function index(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $statuses = $workspace->statuses;

        return view('statuses.index', compact('workspace', 'statuses'));
    }

    /**
     * Shows the status creation form.
     * Only the owner can create statuses.
     */
    public function create(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('statuses.create', compact('workspace'));
    }

    /**
     * Stores the new status.
     */
    public function store(StoreStatusRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $workspace->statuses()->create([
            'name'     => $request->name,
            'color'    => $request->color ?? '#6366f1',
            'position' => $request->position ?? $workspace->statuses()->count() + 1,
        ]);

        return redirect()
            ->route('workspaces.statuses.index', $workspace)
            ->with('success', 'Status created.');
    }

    /**
     * Shows the status edit form.
     */
    public function edit(Workspace $workspace, Status $status): View
    {
        $this->authorize('update', $status);

        return view('statuses.edit', compact('workspace', 'status'));
    }

    /**
     * Updates the status.
     */
    public function update(UpdateStatusRequest $request, Workspace $workspace, Status $status): RedirectResponse
    {
        $this->authorize('update', $status);

        $status->update([
            'name'     => $request->name,
            'color'    => $request->color ?? $status->color,
            'position' => $request->position ?? $status->position,
        ]);

        return redirect()
            ->route('workspaces.statuses.index', $workspace)
            ->with('success', 'Status updated.');
    }

    /**
     * Deletes the status.
     * Linked tasks will have their status_id set to null (nullOnDelete).
     */
    public function destroy(Workspace $workspace, Status $status): RedirectResponse
    {
        $this->authorize('delete', $status);

        $status->delete();

        return redirect()
            ->route('workspaces.statuses.index', $workspace)
            ->with('success', 'Status deleted. Associated tasks no longer have a status.');
    }
}
