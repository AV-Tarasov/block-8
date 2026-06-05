<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Jobs\SendTaskCompletedNotification;


class DispatchTaskCompletedNotification
{

    public function __construct()
    {
    }

    public function __invoke(TaskCompleted $event): void
    {
        SendTaskCompletedNotification::dispatch($event->task);
    }
}
