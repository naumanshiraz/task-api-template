<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CommentRepository
{
    public function __construct(private Comment $model)
    {
    }

    /**
     * Get comments query builder by task ID.
     *
     * @param int $taskId
     * @return Builder
     */
    public function getByTask(int $taskId): Builder
    {
        return $this->model->where('task_id', $taskId)
                           ->orderBy('created_at', 'asc');
    }

    /**
     * Paginate comments by task ID.
     *
     * @param int $taskId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateByTask(int $taskId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getByTask($taskId)->paginate($perPage);
    }

    /**
     * Find a comment by ID.
     *
     * @param int $id
     * @return Comment|null
     */
    public function findById(int $id): ?Comment
    {
        return $this->model->find($id);
    }

    /**
     * Create a new comment.
     *
     * @param array $data
     * @return Comment
     */
    public function create(array $data): Comment
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing comment.
     *
     * @param int $id
     * @param array $data
     * @return Comment|null
     */
    public function update(int $id, array $data): ?Comment
    {
        $comment = $this->findById($id);

        if (!$comment) {
            return null; // or throw ModelNotFoundException
        }

        $comment->update($data);

        return $comment;
    }

    /**
     * Delete a comment by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->find($id)?->delete() ?? false;
    }
}