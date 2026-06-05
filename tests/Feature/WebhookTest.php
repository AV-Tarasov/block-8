<?php

namespace Tests\Feature;

use App\Jobs\SendWebhookJob;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_attempt_is_logged(): void
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $webhook = Webhook::factory()->create();

        $job = new SendWebhookJob(
            $webhook,
            ['event' => 'test']
        );

        $job->handle();

        $this->assertDatabaseHas('webhook_attempts', [
            'webhook_id' => $webhook->id,
            'http_code' => 200,
        ]);
    }

    public function test_webhook_signature_is_sent(): void
    {
        Http::fake();

        $payload = [
            'event' => 'test',
        ];

        $webhook = Webhook::factory()->create([
            'secret' => 'secret-key',
        ]);

        $job = new SendWebhookJob(
            $webhook,
            $payload
        );

        $job->handle();

        Http::assertSent(function ($request) use ($payload) {

            $expected = hash_hmac(
                'sha256',
                json_encode($payload),
                'secret-key'
            );

            return $request->header('X-Signature')[0]
                === $expected;
        });
    }

    public function test_failed_webhook_is_logged(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $webhook = Webhook::factory()->create();

        $this->expectException(\Exception::class);

        (new SendWebhookJob(
            $webhook,
            ['event' => 'test']
        ))->handle();

        $this->assertDatabaseHas('webhook_attempts', [
            'webhook_id' => $webhook->id,
            'status' => false,
        ]);
    }
}
