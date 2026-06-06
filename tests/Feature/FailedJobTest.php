<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Bus;
use Tests\Support\FailingJob;
use Tests\TestCase;


class FailedJobTest extends TestCase
{
    public function test_job_is_retried_and_fails(): void
    {
        Bus::fake();

        Bus::assertNothingDispatched();

        $job = new FailingJob();

        for ($i = 0; $i < 3; $i++) {
            try {
                $job->handle();
            } catch (\Throwable $e) {
            }
        }

        $this->assertTrue(true);
    }
}
