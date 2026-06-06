<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->project
            ->members()
            ->whereKey($user->id)
            ->exists();
    }
    public function update(User $user, Task $task): bool
    {
        return $task->project
            ->members()
            ->whereKey($user->id)
            ->exists();
    }
    public function delete(User $user, Task $task): bool
    {
        return $task->project
            ->members()
            ->whereKey($user->id)
            ->exists();
    }
}
