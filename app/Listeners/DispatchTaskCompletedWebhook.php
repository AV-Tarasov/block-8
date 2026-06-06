<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Jobs\SendWebhookJob;

class DispatchTaskCompletedWebhook
{
    public function handle(TaskCompleted $event): void
    {
        $project = $event->task->project;

        $payload = [
            'event' => 'task.completed',

            'task' => [
                'id' => $event->task->id,
                'title' => $event->task->title,
                'status' => $event->task->status,
            ],

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
