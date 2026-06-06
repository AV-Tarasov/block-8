<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebhookApiTest extends TestCase
{
    public function test_webhook_can_be_created(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $project->members()->attach($user);

        $response = $this->postJson(
            "/api/projects/{$project->id}/webhooks",
            [
                'url' => 'https://example.com/webhook',
                'secret' => 'secret-key',
                'enabled' => true,
            ]
        );

        $response->assertCreated();

        $this->assertDatabaseHas('webhooks', [
            'project_id' => $project->id,
            'url' => 'https://example.com/webhook',
        ]);
    }
}
