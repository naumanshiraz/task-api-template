<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository
{
    public function __construct(private Task $model)
    {
    }

    /**
     * Get query builder for tasks by project with optional filters.
     *
     * @param int $projectId
     * @param array $filters
     * @return Builder
     */
    public function getByProject(int $projectId, array $filters = []): Builder
    {
        $query = $this->model->where('project_id', $projectId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['search'])) {
            $query->whereFullText(['title', 'description'], $filters['search']);
        }

        if (isset($filters['due_after'])) {
            $query->whereDate('due_date', '>=', $filters['due_after']);
        }

        if (isset($filters['due_before'])) {
            $query->whereDate('due_date', '<=', $filters['due_before']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Paginate tasks by project with optional filters.
     *
     * @param int $projectId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $projectId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByProject($projectId, $filters)->paginate($perPage);
    }

    /**
     * Find a task by ID.
     *
     * @param int $id
     * @return Task|null
     */
    public function findById(int $id): ?Task
    {
        return $this->model->find($id);
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @return Task
     */
    public function create(array $data): Task
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing task.
     *
     * @param int $id
     * @param array $data
     * @return Task|null
     */
    public function update(int $id, array $data): ?Task
    {
        $task = $this->findById($id);

        if (!$task) {
            return null; // or throw ModelNotFoundException
        }

        $task->update($data);

        return $task;
    }

    /**
     * Delete a task by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->find($id)?->delete() ?? false;
    }
}