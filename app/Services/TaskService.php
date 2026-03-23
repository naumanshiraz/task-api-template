<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Events\TaskUpdated;
use App\Repositories\TaskRepository;
use App\Models\Task;
use Illuminate\Pagination\Paginator;

class TaskService
{
    public function __construct(
        private TaskRepository $repository,
        private CacheService $cacheService,
    ) {
    }

    public function getProjectTasks(int $projectId, array $filters = [], int $page = 1, int $perPage = 15)
    {
        $cacheKey = "tasks:project:{$projectId}:page:{$page}";

        return $this->cacheService->remember(
            $cacheKey,
            300, // 5 minutes
            fn() => $this->repository->paginate($projectId, $filters, $perPage)
        );
    }

    public function getTaskById(int $taskId): Task
    {
        return $this->repository->findById($taskId) 
            ?? throw new \Exception('Task not found', 404);
    }

    public function createTask(array $data): Task
    {
        $task = $this->repository->create($data);
        $this->cacheService->invalidate("tasks:project:{$data['project_id']}:*");

        return $task;
    }

    public function updateTask(int $taskId, array $data): Task
    {
        $task = $this->getTaskById($taskId);
        $oldStatus = $task->status;
        $oldAssignee = $task->assigned_to;

        $task = $this->repository->update($taskId, $data);

        // Fire events
        if ($task->status !== $oldStatus) {
            TaskUpdated::dispatch($task, $oldStatus);
        }

        if ($task->assigned_to !== $oldAssignee && $task->assigned_to) {
            TaskAssigned::dispatch($task);
        }

        $this->cacheService->invalidate("tasks:project:{$task->project_id}:*");

        return $task;
    }

    public function deleteTask(int $taskId): bool
    {
        $task = $this->getTaskById($taskId);
        $projectId = $task->project_id;

        $deleted = $this->repository->delete($taskId);
        
        if ($deleted) {
            $this->cacheService->invalidate("tasks:project:{$projectId}:*");
        }

        return $deleted;
    }
}