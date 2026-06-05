<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement([
                'new',
                'in_progress',
                'blocked',
                'done',
                'cancelled',
            ]),
            'priority' => fake()->randomElement([
                'low',
                'normal',
                'high',
                'critical',
            ]),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}
