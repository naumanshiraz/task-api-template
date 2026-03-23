<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private NotificationService $service)
    {
    }

    /**
     * Display a listing of all notifications.
     */
    public function index(Request $request)
    {
        try {
            $notifications = $this->service->getUserNotifications(
                $request->user()->id,
                $request->input('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => NotificationResource::collection($notifications),
                'meta' => $this->getPaginationMeta($notifications),
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch notifications', 500);
        }
    }

    /**
     * Display unread notifications.
     */
    public function unread(Request $request)
    {
        try {
            $notifications = $this->service->getUnreadNotifications(
                $request->user()->id,
                $request->input('per_page', 15)
            );

            return response()->json([
                'success' => true,
                'data' => NotificationResource::collection($notifications),
                'meta' => $this->getPaginationMeta($notifications),
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Failed to fetch unread notifications', 500);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, int $notificationId)
    {
        try {
            $notification = $this->service->markAsRead($notificationId);

            return response()->json([
                'success' => true,
                'data' => new NotificationResource($notification),
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return $this->apiError('Notification not found', 404);
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