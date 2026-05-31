<?php

namespace App\Policies;

use App\Models\Queue;
use App\Models\User;
use App\Models\Service;
use App\Models\Business;

class QueuePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Queue $queue): bool
    {
        return true;
    }

    public function create(User $user, Service $service): bool
    {
        return ($user->isManager() && $service->business->user_id === $user->id);
    }

    public function update(User $user, Queue $queue): bool
    {
        return ($user->isManager() && $queue->service->business->user_id === $user->id);
    }

    public function delete(User $user, Queue $queue): bool
    {
        return $user->isAdmin()
            || ($user->isManager() && $queue->service->business->user_id === $user->id);
    }
}