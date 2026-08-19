<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StudentDocument;
use App\Models\User;

class StudentDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY, UserRole::EMPLOYEE);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY);
    }

    public function update(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY, UserRole::EMPLOYEE);
    }

    public function changeStatus(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY, UserRole::EMPLOYEE);
    }
}
