<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Affiche le formulaire de création d'une tâche.
     */
    public function create(Workspace $workspace, Project $project): View
    {
        $this->authorize('view', $project);

        $members  = $workspace->members;
        $statuses = $workspace->statuses;

        return view('tasks.create', compact('workspace', 'project', 'members', 'statuses'));
    }

    /**
     * Enregistre la nouvelle tâche dans le projet.
     */
    public function store(StoreTaskRequest $request, Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $project->tasks()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'status_id'   => $request->status_id,
            'assigned_to' => $request->assigned_to,
            'due_date'    => $request->due_date,
        ]);

        return redirect()
            ->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Tâche créée avec succès.');
    }

    /**
     * Affiche le détail d'une tâche.
     * Route shallow : on remonte workspace/project depuis la tâche.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['status', 'assignee', 'comments.author']);

        $project   = $task->project;
        $workspace = $project->workspace;

        return view('tasks.show', compact('workspace', 'project', 'task'));
    }

    /**
     * Affiche le formulaire d'édition d'une tâche.
     */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $project   = $task->project;
        $workspace = $project->workspace;
        $members   = $workspace->members;
        $statuses  = $workspace->statuses;

        return view('tasks.edit', compact('workspace', 'project', 'task', 'members', 'statuses'));
    }

    /**
     * Met à jour la tâche.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'status_id'   => $request->status_id,
            'assigned_to' => $request->assigned_to,
            'due_date'    => $request->due_date,
        ]);

        $project   = $task->project;
        $workspace = $project->workspace;

        return redirect()
            ->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Tâche mise à jour.');
    }

    /**
     * Supprime la tâche.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $project   = $task->project;
        $workspace = $project->workspace;

        $task->delete();

        return redirect()
            ->route('workspaces.projects.show', [$workspace, $project])
            ->with('success', 'Tâche supprimée.');
    }
}
