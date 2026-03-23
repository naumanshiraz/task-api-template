<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Jobs\ProcessNotification;

class SendTaskAssignedNotification
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
    public function handle(TaskAssigned $event): void
    {
        if ($event->task->assigned_to) {
            ProcessNotification::dispatch(
                userId: $event->task->assigned_to,
                type: 'task_assigned',
                message: "You have been assigned to task: {$event->task->title}",
                notifiableType: 'App\Models\Task',
                notifiableId: $event->task->id,
            );
        }
    }
}