<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function view(User $user, User $targetUser): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function toggleStatus(User $user, User $targetUser): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function resetPassword(User $user, User $targetUser): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
