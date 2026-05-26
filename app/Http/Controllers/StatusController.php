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
     * Liste les statuts du workspace.
     * Seuls les membres peuvent voir la liste.
     */
    public function index(Workspace $workspace): View
    {
        $this->authorize('view', $workspace);

        $statuses = $workspace->statuses;

        return view('statuses.index', compact('workspace', 'statuses'));
    }

    /**
     * Affiche le formulaire de création d'un statut.
     * Seul le propriétaire peut créer des statuts.
     */
    public function create(Workspace $workspace): View
    {
        $this->authorize('update', $workspace);

        return view('statuses.create', compact('workspace'));
    }

    /**
     * Enregistre le nouveau statut.
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
            ->with('success', 'Statut créé.');
    }

    /**
     * Affiche le formulaire d'édition d'un statut.
     */
    public function edit(Workspace $workspace, Status $status): View
    {
        $this->authorize('update', $status);

        return view('statuses.edit', compact('workspace', 'status'));
    }

    /**
     * Met à jour le statut.
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
            ->with('success', 'Statut mis à jour.');
    }

    /**
     * Supprime le statut.
     * Les tâches liées auront leur status_id mis à null (nullOnDelete).
     */
    public function destroy(Workspace $workspace, Status $status): RedirectResponse
    {
        $this->authorize('delete', $status);

        $status->delete();

        return redirect()
            ->route('workspaces.statuses.index', $workspace)
            ->with('success', 'Statut supprimé. Les tâches associées n\'ont plus de statut.');
    }
}
