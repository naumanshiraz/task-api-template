<?php

namespace App\Services;

use App\Repositories\CommentRepository;
use App\Models\Comment;

class CommentService
{
    public function __construct(private CommentRepository $repository)
    {
    }

    public function getTaskComments(int $taskId, int $perPage = 15)
    {
        return $this->repository->paginateByTask($taskId, $perPage);
    }

    public function getCommentById(int $commentId): Comment
    {
        return $this->repository->findById($commentId)
            ?? throw new \Exception('Comment not found', 404);
    }

    public function createComment(array $data): Comment
    {
        return $this->repository->create($data);
    }

    public function updateComment(int $commentId, array $data): Comment
    {
        return $this->repository->update($commentId, $data);
    }

    public function deleteComment(int $commentId): bool
    {
        return $this->repository->delete($commentId);
    }
}