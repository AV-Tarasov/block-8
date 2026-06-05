<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Project;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_project(): void
    {
        $owner = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/projects/{$project->id}")
            ->assertOk();
    }

    public function test_member_can_view_project(): void
    {
        $owner = User::factory()->create();

        $member = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $project->members()->attach($member->id);

        Sanctum::actingAs($member);

        $this->getJson("/api/projects/{$project->id}")
            ->assertOk();
    }

    public function test_stranger_cannot_view_project(): void
    {
        $owner = User::factory()->create();

        $stranger = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($stranger);

        $this->getJson("/api/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_member_cannot_delete_project(): void
    {
        $owner = User::factory()->create();

        $member = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $project->members()->attach($member->id);

        Sanctum::actingAs($member);

        $this->deleteJson("/api/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_owner_can_delete_project(): void
    {
        $owner = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson("/api/projects/{$project->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_guest_cannot_view_project(): void
    {
        $project = Project::factory()->create();

        $this->getJson("/api/projects/{$project->id}")
            ->assertUnauthorized();
    }
}
