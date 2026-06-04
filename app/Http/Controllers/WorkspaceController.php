<?php

namespace App\Http\Controllers;

use App\Events\WorkspaceCreated;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Models\Comment;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class WorkspaceController extends Controller
{
    /**
     * Lists all workspaces the user is a member of.
     */
    public function index(): View
    {
        $workspaces = auth()->user()
            ->workspaces()
            ->latest()
            ->get();

        return view('workspaces.index', compact('workspaces'));
    }

    /**
     * Shows the creation form.
     */
    public function create(): View
    {
        return view('workspaces.create');
    }

    /**
     * Stores the new workspace.
     */
    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $workspace = Workspace::create([
            'name'        => $request->name,
            'description' => $request->description,
            'owner_id'    => auth()->id(),
        ]);

        // The owner is also added as a member with the 'owner' role
        $workspace->members()->attach(auth()->id(), ['role' => 'owner']);

        WorkspaceCreated::dispatch($workspace, auth()->user());

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Shows a workspace, its members, its projects and recent activity.
     * Only members can access this page.
     */
    public function show(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $workspace->load('members', 'owner', 'projects');

        // Retrieves the 20 most recent actions on this workspace's projects and tasks
        $projectIds = $workspace->projects->pluck('id');
        $taskIds    = $projectIds->isNotEmpty()
            ? Task::whereIn('project_id', $projectIds)->pluck('id')
            : collect();

        $commentIds = $taskIds->isNotEmpty()
            ? Comment::whereIn('task_id', $taskIds)->pluck('id')
            : collect();

        $activities = Activity::query()
            ->where(function ($q) use ($projectIds, $taskIds, $commentIds) {
                $q->where(function ($inner) use ($projectIds) {
                    $inner->where('subject_type', \App\Models\Project::class)
                          ->whereIn('subject_id', $projectIds);
                })->orWhere(function ($inner) use ($taskIds) {
                    $inner->where('subject_type', \App\Models\Task::class)
                          ->whereIn('subject_id', $taskIds);
                })->orWhere(function ($inner) use ($commentIds) {
                    $inner->where('subject_type', Comment::class)
                          ->whereIn('subject_id', $commentIds);
                });
            })
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        return view('workspaces.show', compact('workspace', 'activities'));
    }

    /**
     * Shows the edit form.
     * Only the owner can edit the workspace.
     */
    public function edit(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Updates the workspace.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('update', $workspace);

        $workspace->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated.');
    }

    /**
     * Deletes the workspace.
     * The database cascade automatically removes the associated members.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()
            ->route('workspaces.index')
            ->with('success', 'Workspace deleted.');
    }
}
