<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_be_created(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', [
            'name' => 'New Project',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'owner_id' => $user->id,
        ]);
    }

    public function test_project_name_is_required(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/projects', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_projects_can_be_listed(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Project::factory()->count(3)->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_projects_list_excludes_inaccessible_projects(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        Project::factory()->count(2)->create([
            'owner_id' => $user->id,
        ]);

        Project::factory()->count(2)->create();

        $response = $this->getJson('/api/projects');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_project_can_be_viewed(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $project->id);
    }

    public function test_project_can_be_updated(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'name' => 'Updated Project',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
        ]);
    }

    public function test_project_can_be_deleted(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_task_can_be_created(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $response = $this->postJson(
            "/api/projects/{$project->id}/tasks",
            [
                'title' => 'New Task',
                'description' => 'Test description',
                'status' => 'new',
                'priority' => 'normal',
            ]
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
        ]);
    }

    public function test_stranger_cannot_list_tasks_for_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($stranger);

        $this->getJson("/api/projects/{$project->id}/tasks")
            ->assertForbidden();
    }

    public function test_stranger_cannot_create_task_for_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        Sanctum::actingAs($stranger);

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Stranger task',
            'status' => 'new',
            'priority' => 'normal',
        ])->assertForbidden();
    }

    public function test_member_can_list_tasks_but_cannot_change_them(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $project->members()->attach($member->id);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'new',
        ]);

        Sanctum::actingAs($member);

        $this->getJson("/api/projects/{$project->id}/tasks")
            ->assertOk();

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Member task',
            'status' => 'new',
            'priority' => 'normal',
        ])->assertForbidden();

        $this->patchJson("/api/projects/{$project->id}/tasks/{$task->id}", [
            'status' => 'done',
        ])->assertForbidden();

        $this->deleteJson("/api/projects/{$project->id}/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_tasks_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'done',
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'new',
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?status=done"
        );

        $response->assertOk();

        $this->assertCount(
            1,
            $response->json('data')
        );
    }

    public function test_tasks_can_be_filtered_by_priority(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'priority' => 'critical',
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?priority=critical"
        );

        $response->assertOk();

        $this->assertCount(
            1,
            $response->json('data')
        );
    }

    public function test_tasks_can_be_searched_by_title(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Implement Auth',
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Create Dashboard',
        ]);

        $response = $this->getJson(
            "/api/projects/{$project->id}/tasks?search=Auth"
        );

        $response->assertOk();

        $this->assertCount(
            1,
            $response->json('data')
        );
    }
}
