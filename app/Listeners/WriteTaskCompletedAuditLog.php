<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WriteTaskCompletedAuditLog
{
    public function __construct()
    {
        //
    }


    public function handle(TaskCompleted $event): void
    {
        AuditLog::create([
            'entity_type' => 'task',
            'entity_id' => $event->task->id,
            'action' => 'completed',
            'meta' => [],
            'occurred_at' => now(),
        ]);
    }
}
