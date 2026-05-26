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
     * Affiche le formulaire de création d'un projet.
     * Seuls les membres du workspace peuvent créer un projet.
     */
    public function create(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        return view('projects.create', compact('workspace'));
    }

    /**
     * Enregistre le nouveau projet dans le workspace.
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
            ->with('success', 'Projet créé avec succès.');
    }

    /**
     * Affiche un projet et ses informations.
     * Scoped binding : Laravel vérifie que le projet appartient bien au workspace.
     */
    public function show(Request $request, Workspace $workspace, Project $project): View
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with(['assignee', 'status']);

        // Filtre par statut
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filtre par membre assigné
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Recherche par titre
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks    = $query->latest()->get();
        $statuses = $workspace->statuses;
        $members  = $workspace->members;

        return view('projects.show', compact('workspace', 'project', 'tasks', 'statuses', 'members'));
    }

    /**
     * Affiche le formulaire d'édition d'un projet.
     */
    public function edit(Workspace $workspace, Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('workspace', 'project'));
    }

    /**
     * Met à jour le projet.
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
            ->with('success', 'Projet mis à jour.');
    }

    /**
     * Supprime le projet.
     * La cascade en base supprimera les tâches associées (Phase 4).
     */
    public function destroy(Workspace $workspace, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', 'Projet supprimé.');
    }
}
