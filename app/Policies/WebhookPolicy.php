<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Webhook;

class WebhookPolicy
{
    public function view(User $user, Webhook $webhook): bool
    {
        return
            $webhook->project->owner_id === $user->id
            ||
            $webhook->project
                ->members()
                ->whereKey($user->id)
                ->exists();
    }

    public function delete(User $user, Webhook $webhook): bool
    {
        return $webhook->project->owner_id === $user->id;
    }
}
