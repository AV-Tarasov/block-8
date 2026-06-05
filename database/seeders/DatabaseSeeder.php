<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $users = User::factory(3)->create();

        $projects = Project::factory(2)
            ->recycle($users)
            ->create();

        $tasks = Task::factory(10)
            ->recycle($projects)
            ->create();

        Comment::factory(20)
            ->recycle($users)
            ->recycle($tasks)
            ->create();
    }
}
