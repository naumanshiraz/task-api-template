<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use App\Models\Notification;

class NotificationService
{
    public function __construct(
        private NotificationRepository $repository,
        private CacheService $cacheService,
    ) {
    }

    public function getUserNotifications(int $userId, int $perPage = 15)
    {
        return $this->repository->paginateByUser($userId, $perPage);
    }

    public function getUnreadNotifications(int $userId, int $perPage = 15)
    {
        $cacheKey = "notifications:unread:{$userId}";

        return $this->cacheService->remember(
            $cacheKey,
            60,
            fn() => $this->repository->paginateUnreadByUser($userId, $perPage)
        );
    }

    public function createNotification(array $data): Notification
    {
        $notification = $this->repository->create($data);
        
        $this->cacheService->invalidate("notifications:unread:{$data['user_id']}");

        return $notification;
    }

    public function markAsRead(int $notificationId): Notification
    {
        $notification = $this->repository->findById($notificationId)
            ?? throw new \Exception('Notification not found', 404);

        $notification = $this->repository->markAsRead($notificationId);
        
        $this->cacheService->invalidate("notifications:unread:{$notification->user_id}");

        return $notification;
    }
}