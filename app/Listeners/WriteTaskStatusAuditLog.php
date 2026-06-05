<?php

namespace App\Listeners;

use App\Events\TaskStatusChanged;
use App\Models\AuditLog;

class WriteTaskStatusAuditLog
{
    public function __construct()
    {
    }

    public function __invoke(TaskStatusChanged $event): void
    {

        AuditLog::create([
            'entity_type' => 'task',
            'entity_id' => $event->task->id,
            'action' => 'status_changed',

            'meta' => [
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ],

            'occurred_at' => now(),
        ]);
    }
}
