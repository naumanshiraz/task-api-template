<?php

namespace App\Services;

use App\Repositories\ProjectRepository;
use App\Models\Project;

class ProjectService
{
    public function __construct(private ProjectRepository $repository)
    {
    }

    public function getUserProjects(int $userId, int $perPage = 15)
    {
        return $this->repository->paginateByUser($userId, $perPage);
    }

    public function getProjectById(int $projectId): Project
    {
        return $this->repository->findById($projectId)
            ?? throw new \Exception('Project not found', 404);
    }

    public function createProject(array $data): Project
    {
        return $this->repository->create($data);
    }

    public function updateProject(int $projectId, array $data): Project
    {
        return $this->repository->update($projectId, $data);
    }

    public function deleteProject(int $projectId): bool
    {
        return $this->repository->delete($projectId);
    }
}