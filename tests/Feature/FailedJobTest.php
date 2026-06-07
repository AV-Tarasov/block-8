<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\Support\FailingJob;
use Tests\TestCase;


class FailedJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_retried_and_fails(): void
    {
        config(['queue.default' => 'database']);

        FailingJob::dispatch();

        for ($i = 0; $i < 3; $i++) {
            Artisan::call('queue:work', [
                'connection' => 'database',
                '--once' => true,
                '--tries' => 3,
            ]);
        }

        $this->assertDatabaseHas('failed_jobs', [
            'queue' => 'default',
        ]);
    }
}
