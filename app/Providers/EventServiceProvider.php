<?php

namespace App\Providers;

use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Listeners\DispatchTaskCompletedNotification;
use App\Listeners\DispatchTaskCompletedWebhook;
use App\Listeners\DispatchWebhookListener;
use App\Listeners\WriteTaskCompletedAuditLog;
use App\Listeners\WriteTaskCreatedAuditLog;
use App\Listeners\WriteTaskStatusAuditLog;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskStatusChanged::class => [
            WriteTaskStatusAuditLog::class,
            DispatchWebhookListener::class,
        ],

        TaskCompleted::class => [
            DispatchTaskCompletedNotification::class,
            WriteTaskCompletedAuditLog::class,
            DispatchTaskCompletedWebhook::class,
        ],

        TaskCreated::class => [
            WriteTaskCreatedAuditLog::class,
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
