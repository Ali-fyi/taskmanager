<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Shows the project creation form.
     * Only workspace members can create a project.
     */
    public function create(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        return view('projects.create', compact('workspace'));
    }

    /**
     * Stores the new project in the workspace.
     */
    public function store(StoreProjectRequest $request, Workspace $workspace): RedirectResponse
    {
        $this->authorize('view', $workspace);

        $project = $workspace->projects()->create([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? '#6366f1',
        ]);

        return redirect()
            ->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Project created successfully.');
    }

    /**
     * Shows a project and its information.
     * Scoped binding: Laravel checks that the project belongs to the workspace.
     */
    public function show(Request $request, Workspace $workspace, Project $project): View
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with(['assignee', 'status']);

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by assigned member
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks    = $query->latest()->get();
        $statuses = $workspace->statuses;
        $members  = $workspace->members;

        return view('projects.show', compact('workspace', 'project', 'tasks', 'statuses', 'members'));
    }

    /**
     * Shows the project edit form.
     */
    public function edit(Workspace $workspace, Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('workspace', 'project'));
    }

    /**
     * Updates the project.
     */
    public function update(UpdateProjectRequest $request, Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? $project->color,
        ]);

        return redirect()
            ->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Project updated.');
    }

    /**
     * Deletes the project.
     * The database cascade will remove the associated tasks (Phase 4).
     */
    public function destroy(Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', 'Project deleted.');
    }
}
