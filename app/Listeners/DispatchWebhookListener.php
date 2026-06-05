<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Jobs\SendWebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchWebhookListener
{
    public function __construct()
    {
    }

    public function handle(TaskStatusChanged $event): void
    {
        $project = $event->task->project;

        $payload = [
            'event' => 'task.status_changed',

            'task' => [
                'id' => $event->task->id,
                'title' => $event->task->title,
                'status' => $event->task->status,
            ],

            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,

            'occurred_at' => now()->toISOString(),
        ];

        foreach ($project->webhooks as $webhook) {

            if (!$webhook->enabled) {
                continue;
            }

            SendWebhookJob::dispatch(
                $webhook,
                $payload
            );
        }
    }
}
