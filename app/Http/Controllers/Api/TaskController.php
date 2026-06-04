<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    /**
     * Paginated list of tasks in the user's workspaces.
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
     * Shows a task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        $task->load(['status', 'assignee', 'project.workspace']);

        return new TaskResource($task);
    }

    /**
     * Creates a task in the specified project.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $project = Project::findOrFail($request->project_id);

        $this->authorize('view', $project);

        $task = $this->taskService->create(
            $project,
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'message' => 'Task created successfully.',
            'data'    => new TaskResource($task->load(['status', 'assignee', 'project'])),
        ], 201);
    }

    /**
     * Updates a task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->update(
            $task,
            $request->validated(),
            $request->user(),
        );

        return response()->json([
            'message' => 'Task updated.',
            'data'    => new TaskResource($task->load(['status', 'assignee', 'project'])),
        ]);
    }

    /**
     * Deletes a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->json(['message' => 'Task deleted.'], 200);
    }
}
