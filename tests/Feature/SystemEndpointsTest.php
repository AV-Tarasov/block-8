<?php

namespace Tests\Feature;


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class SystemEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_ready_endpoint_returns_ready(): void
    {
        Redis::shouldReceive('ping')
            ->once()
            ->andReturn('PONG');

        $this->getJson('/api/ready')
            ->assertOk()
            ->assertJson([
                'status' => 'ready',
            ]);
    }

    public function test_metrics_endpoint_returns_metrics(): void
    {
        $this->getJson('/api/metrics')
            ->assertOk()
            ->assertJsonStructure([
                'users_total',
                'projects_total',
                'tasks_total',
                'comments_total',
                'memory_usage_mb',
            ]);
    }
}
