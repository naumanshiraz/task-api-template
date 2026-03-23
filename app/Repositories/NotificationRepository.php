<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository
{
    public function __construct(private Notification $model)
    {
    }

    /**
     * Get unread notifications query builder for a user.
     *
     * @param int $userId
     * @return Builder
     */
    public function getUnreadByUser(int $userId): Builder
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Paginate unread notifications for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateUnreadByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->getUnreadByUser($userId)->paginate($perPage);
    }

    /**
     * Get all notifications query builder for a user.
     *
     * @param int $userId
     * @return Builder
     */
    public function getByUser(int $userId): Builder
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Paginate all notifications for a user.
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
     * Find a notification by ID.
     *
     * @param int $id
     * @return Notification|null
     */
    public function findById(int $id): ?Notification
    {
        return $this->model->find($id);
    }

    /**
     * Create a new notification.
     *
     * @param array $data
     * @return Notification
     */
    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id
     * @return Notification|null
     */
    public function markAsRead(int $id): ?Notification
    {
        $notification = $this->findById($id);

        if (!$notification) {
            return null; // or throw ModelNotFoundException
        }

        $notification->markAsRead();

        return $notification;
    }
}