<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\AuditLog;


class WriteTaskCreatedAuditLog
{
    public function __construct()
    {
    }

    public function handle(TaskCreated $event): void
    {
        AuditLog::create([
            'entity_type' => 'task',
            'entity_id' => $event->task->id,
            'action' => 'created',
            'meta' => [],
            'occurred_at' => now(),
        ]);
    }
}
