<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Jobs\ProcessNotification;

class SendTaskUpdatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskUpdated $event): void
    {
        // Notify project owner about task status change
        $projectOwnerId = $event->task->project->user_id;

        ProcessNotification::dispatch(
            userId: $projectOwnerId,
            type: 'task_updated',
            message: "Task '{$event->task->title}' status changed from {$event->previousStatus} to {$event->task->status}",
            notifiableType: 'App\Models\Task',
            notifiableId: $event->task->id,
            data: [
                'old_status' => $event->previousStatus,
                'new_status' => $event->task->status,
            ],
        );
    }
}