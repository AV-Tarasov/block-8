<?php

namespace App\Jobs;

use App\Models\SentNotification;
use App\Models\Task;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


class SendTaskCompletedNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [5, 10, 30];

    public function __construct(
        public Task $task
    )
    {
    }

    public function handle(): void
    {
        SentNotification::query()->insertOrIgnore([
            'task_id' => $this->task->id,
            'type' => 'task_completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        File::append(
            storage_path('logs/notifications.log'),
            sprintf(
                "[%s] Task #%d completed: %s\n",
                now(),
                $this->task->id,
                $this->task->title
            )
        );
    }
}
