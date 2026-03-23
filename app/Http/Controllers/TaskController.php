<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private TaskService $taskService,
        private ProjectService $projectService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $projectId)
    {
        try {
            $project = $this->projectService->getProjectById($projectId);
            $this->authorize('view', $project);

            $filters = $request->only(['status', 'assigned_to', 'search', 'due_after', 'due_before']);
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 15);

            $tasks = $this->taskService->getProjectTasks($projectId, $filters, $page, $perPage);

            return response()->json([
                'success' => true,
                'data' => TaskResource::collection($tasks),
                'meta' => $this->getPaginationMeta($tasks),
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch tasks', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, int $projectId)
    {
        try {
            $project = $this->projectService->getProjectById($projectId);
            $this->authorize('create', [\App\Models\Task::class, $project]);

            $validated = $request->validated();
            $validated['project_id'] = $projectId;

            $task = $this->taskService->createTask($validated);

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'message' => 'Task created successfully',
            ], 201);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Failed to create task', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $projectId, int $taskId)
    {
        try {
            $project = $this->projectService->getProjectById($projectId);
            $this->authorize('view', $project);

            $task = $this->taskService->getTaskById($taskId);

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Task not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, int $projectId, int $taskId)
    {
        try {
            $project = $this->projectService->getProjectById($projectId);
            $task = $this->taskService->getTaskById($taskId);

            $this->authorize('update', $task);

            $validated = $request->validated();
            $task = $this->taskService->updateTask($taskId, $validated);

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'message' => 'Task updated successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Task not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $projectId, int $taskId)
    {
        try {
            $task = $this->taskService->getTaskById($taskId);
            $this->authorize('delete', $task);

            $this->taskService->deleteTask($taskId);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->apiError('Unauthorized', 403);
        } catch (\Exception $e) {
            return $this->apiError('Task not found', 404);
        }
    }

    /**
     * Get pagination metadata.
     */
    protected function getPaginationMeta($paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}