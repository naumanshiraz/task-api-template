<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $type,
        public string $message,
        public string $notifiableType,
        public int $notifiableId,
        public array $data = [],
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        $service->createNotification([
            'user_id' => $this->userId,
            'type' => $this->type,
            'message' => $this->message,
            'notifiable_type' => $this->notifiableType,
            'notifiable_id' => $this->notifiableId,
            'data' => $this->data,
        ]);
    }
}