<?php

namespace App\Console\Commands;

use App\Models\WebhookAttempt;
use Illuminate\Console\Command;

class CleanupWebhookAttempts extends Command
{
    protected $signature = 'webhooks:cleanup';

    protected $description = 'Delete old webhook attempts';

    public function handle(): int
    {
        $deleted = WebhookAttempt::query()
            ->where(
                'created_at',
                '<',
                now()->subDays(30)
            )
            ->delete();

        $this->info("Deleted {$deleted} records");

        return self::SUCCESS;
    }
}
