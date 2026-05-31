<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Models\Business;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Service $service): bool
    {
        return true;
    }

    public function create(User $user, Business $business): bool
    {
        return ($user->isManager() && $business->user_id === $user->id);
    }

    public function update(User $user, Service $service): bool
    {
        return ($user->isManager() && $service->business->user_id === $user->id);
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->isAdmin()
            || ($user->isManager() && $service->business->user_id === $user->id);
    }
}