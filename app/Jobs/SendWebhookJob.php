<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendWebhookJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public array $backoff = [5, 10, 30];

    public function __construct(
        public Webhook $webhook,
        public array   $payload
    ){}

    public function handle(): void
    {
        $startedAt = microtime(true);

        try {
            $signature = hash_hmac(
                'sha256',
                json_encode($this->payload),
                $this->webhook->secret
            );

            $response = Http::withHeaders([
                'X-Signature' => $signature,
                'Idempotency-Key' => sha1(
                    $this->webhook->id .
                    json_encode($this->payload)
                ),
            ])->post(
                $this->webhook->url,
                $this->payload
            );

            if ($response->failed()) {
                WebhookAttempt::create([
                    'webhook_id' => $this->webhook->id,
                    'status' => false,
                    'http_code' => $response->status(),
                    'response_time' => (microtime(true) - $startedAt) * 1000,
                    'error' => 'Webhook failed with status ' . $response->status(),
                ]);

                throw new \Exception('Webhook failed');
            }

            WebhookAttempt::create([
                'webhook_id' => $this->webhook->id,
                'status' => true,
                'http_code' => $response->status(),
                'response_time' => (microtime(true) - $startedAt) * 1000,
                'error' => null,
            ]);

        } catch (\Throwable $e) {

            if (!isset($response)) {
                WebhookAttempt::create([
                    'webhook_id' => $this->webhook->id,
                    'status' => false,
                    'http_code' => null,
                    'response_time' => (microtime(true) - $startedAt) * 1000,
                    'error' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}
