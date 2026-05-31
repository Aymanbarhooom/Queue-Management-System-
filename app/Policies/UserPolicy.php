<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Business;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }
    
    public function view(User $user): bool
    {
        return $user->isAdmin();
    }

    public function addManager(User $user): bool
    {
        return $user->isAdmin();
    }
    public function can_deposit(User $user): bool
    {
        return $user->isAdmin();
    }
}