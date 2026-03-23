<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProjectRepository
{
    public function __construct(private Project $model)
    {
    }

    /**
     * Get query builder for projects by user.
     *
     * @param int $userId
     * @return Builder
     */
    public function getByUser(int $userId): Builder
    {
        return $this->model->where('user_id', $userId);
    }

    /**
     * Paginate projects by user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByUser($userId)->paginate($perPage);
    }

    /**
     * Find a project by ID.
     *
     * @param int $id
     * @return Project|null
     */
    public function findById(int $id): ?Project
    {
        return $this->model->find($id);
    }

    /**
     * Create a new project.
     *
     * @param array $data
     * @return Project
     */
    public function create(array $data): Project
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing project.
     *
     * @param int $id
     * @param array $data
     * @return Project|null
     */
    public function update(int $id, array $data): ?Project
    {
        $project = $this->findById($id);

        if (!$project) {
            return null; // or throw ModelNotFoundException
        }

        $project->update($data);

        return $project;
    }

    /**
     * Delete a project by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->find($id)?->delete() ?? false;
    }
}