<?php

namespace App\Http\Controllers;

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
     * Liste tous les workspaces dont l'utilisateur est membre.
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
     * Affiche le formulaire de création.
     */
    public function create(): View
    {
        return view('workspaces.create');
    }

    /**
     * Enregistre le nouveau workspace.
     */
    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $workspace = Workspace::create([
            'name'        => $request->name,
            'description' => $request->description,
            'owner_id'    => auth()->id(),
        ]);

        // L'owner est aussi ajouté comme membre avec le rôle 'owner'
        $workspace->members()->attach(auth()->id(), ['role' => 'owner']);

        // Statuts par défaut créés automatiquement pour chaque nouveau workspace
        $workspace->statuses()->createMany([
            ['name' => 'À faire',  'color' => '#6366f1', 'position' => 1],
            ['name' => 'En cours', 'color' => '#f59e0b', 'position' => 2],
            ['name' => 'Terminé',  'color' => '#10b981', 'position' => 3],
        ]);

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', 'Workspace créé avec succès.');
    }

    /**
     * Affiche un workspace, ses membres, ses projets et l'activité récente.
     * Seuls les membres peuvent accéder à cette page.
     */
    public function show(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $workspace->load('members', 'owner', 'projects');

        // Récupère les 20 dernières actions sur les projets et tâches de ce workspace
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
     * Affiche le formulaire d'édition.
     * Seul le propriétaire peut modifier le workspace.
     */
    public function edit(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Met à jour le workspace.
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
            ->with('success', 'Workspace mis à jour.');
    }

    /**
     * Supprime le workspace.
     * La cascade en base supprime automatiquement les membres associés.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()
            ->route('workspaces.index')
            ->with('success', 'Workspace supprimé.');
    }
}
