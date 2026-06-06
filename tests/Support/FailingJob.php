<?php

namespace Tests\Support;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FailingJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public function handle(): void
    {
        throw new \Exception('Job failed intentionally');
    }
}
