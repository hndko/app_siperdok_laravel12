<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateProjectStatusNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public int $projectId,
        public int $userId,
        public int $actorId,
        public string $event,
        public string $title,
        public string $message,
        public string $type = 'info',
    ) {}

    public function handle(): void
    {
        Notification::create([
            'user_id' => $this->userId,
            'project_id' => $this->projectId,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to create project status notification.', [
            'project_id' => $this->projectId,
            'user_id' => $this->userId,
            'actor_id' => $this->actorId,
            'event' => $this->event,
            'error' => $exception?->getMessage(),
        ]);
    }
}
