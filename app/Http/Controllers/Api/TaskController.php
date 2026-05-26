<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * Liste paginée des tâches dans les workspaces de l'utilisateur.
     */
    public function index(): AnonymousResourceCollection
    {
        $workspaceIds = auth()->user()->workspaces()->pluck('workspaces.id');

        $projectIds = Project::whereIn('workspace_id', $workspaceIds)->pluck('id');

        $tasks = Task::whereIn('project_id', $projectIds)
            ->with(['status', 'assignee', 'project.workspace'])
            ->latest()
            ->paginate(20);

        return TaskResource::collection($tasks);
    }

    /**
     * Affiche une tâche.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        $task->load(['status', 'assignee', 'project.workspace']);

        return new TaskResource($task);
    }

    /**
     * Crée une tâche dans le projet spécifié.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $project = Project::findOrFail($request->project_id);

        // Vérifier que l'utilisateur est membre du workspace du projet
        $this->authorize('view', $project);

        $task = $project->tasks()->create($request->validated());

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'data'    => new TaskResource($task->load(['status', 'assignee', 'project'])),
        ], 201);
    }

    /**
     * Met à jour une tâche.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json([
            'message' => 'Tâche mise à jour.',
            'data'    => new TaskResource($task->load(['status', 'assignee', 'project'])),
        ]);
    }

    /**
     * Supprime une tâche.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Tâche supprimée.'], 200);
    }
}
