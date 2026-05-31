<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    
    public function view(User $user, Business $business): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function update(User $user, Business $business): bool
    {
        return($user->isManager() && $business->user_id === $user->id);
    }

    public function delete(User $user, Business $business): bool
    {
        return ($user->isManager() && $business->user_id === $user->id);
    }
}