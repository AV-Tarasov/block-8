<?php

namespace Tests\Feature;

use App\Events\TaskCompleted;
use App\Jobs\SendTaskCompletedNotification;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Support\Facades\File;

class TaskCompletedJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_completed_dispatches_notification_job(): void
    {
        Queue::fake();

        $task = Task::factory()->create();

        event(new TaskCompleted($task));

        Queue::assertPushed(
            SendTaskCompletedNotification::class
        );
    }

    public function test_notification_is_not_sent_twice(): void
    {
        $task = Task::factory()->create();

        $job = new SendTaskCompletedNotification($task);

        $job->handle();
        $job->handle();

        $this->assertDatabaseCount(
            'sent_notifications',
            1
        );
    }

    public function test_notification_is_written_to_log(): void
    {
        File::delete(
            storage_path('logs/notifications.log')
        );

        $task = Task::factory()->create();

        (new SendTaskCompletedNotification($task))
            ->handle();

        $this->assertTrue(
            File::exists(
                storage_path('logs/notifications.log')
            )
        );
    }
}
