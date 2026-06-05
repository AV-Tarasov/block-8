<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;

class TaskAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $this->postJson(
            "/api/projects/{$project->id}/tasks",
            [
                'title' => 'New task',
                'status' => 'new',
                'priority' => 'normal',
            ]
        )->assertSuccessful();

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'task',
            'action' => 'created',
        ]);
    }

    public function test_task_status_change_creates_audit_log(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $project = Project::factory()->create();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'new',
        ]);

        $this->patchJson("/api/projects/{$project->id}/tasks/{$task->id}", [
            'status' => 'done',
        ])->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_task_completion_creates_audit_log(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $project = Project::factory()->create([
            'owner_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => 'in_progress',
        ]);

        $this->patchJson(
            "/api/projects/{$project->id}/tasks/{$task->id}",
            [
                'status' => 'done',
            ]
        )->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'action' => 'completed',
        ]);
    }
}
