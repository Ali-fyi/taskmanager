<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProjectRequest;
use App\Http\Requests\Api\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Liste paginée des projets appartenant aux workspaces de l'utilisateur.
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceIds = auth()->user()->workspaces()->pluck('workspaces.id');

        $projects = Project::whereIn('workspace_id', $workspaceIds)
            ->with('workspace')
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    /**
     * Affiche un projet et ses tâches.
     */
    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        $project->load(['workspace', 'tasks.status', 'tasks.assignee']);

        return new ProjectResource($project);
    }

    /**
     * Crée un projet dans le workspace spécifié.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $workspace = Workspace::findOrFail($request->workspace_id);

        // Vérifier que l'utilisateur est membre du workspace
        $this->authorize('view', $workspace);

        $project = $workspace->projects()->create([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? '#6366f1',
        ]);

        return response()->json([
            'message' => 'Projet créé avec succès.',
            'data'    => new ProjectResource($project->load('workspace')),
        ], 201);
    }

    /**
     * Met à jour un projet.
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? $project->color,
        ]);

        return response()->json([
            'message' => 'Projet mis à jour.',
            'data'    => new ProjectResource($project->load('workspace')),
        ]);
    }

    /**
     * Supprime un projet.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Projet supprimé.'], 200);
    }
}
