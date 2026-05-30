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
     * Paginated list of projects belonging to the user's workspaces.
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
     * Shows a project and its tasks.
     */
    public function show(Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        $project->load(['workspace', 'tasks.status', 'tasks.assignee']);

        return new ProjectResource($project);
    }

    /**
     * Creates a project in the specified workspace.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $workspace = Workspace::findOrFail($request->workspace_id);

        // Check that the user is a member of the workspace
        $this->authorize('view', $workspace);

        $project = $workspace->projects()->create([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? '#6366f1',
        ]);

        return response()->json([
            'message' => 'Project created successfully.',
            'data'    => new ProjectResource($project->load('workspace')),
        ], 201);
    }

    /**
     * Updates a project.
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
            'message' => 'Project updated.',
            'data'    => new ProjectResource($project->load('workspace')),
        ]);
    }

    /**
     * Deletes a project.
     */
    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted.'], 200);
    }
}
