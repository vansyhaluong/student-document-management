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
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY)
            || $this->isAssignedEmployee($user, $document);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY);
    }

    public function update(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY)
            || $this->isAssignedEmployee($user, $document);
    }

    public function assign(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY);
    }

    public function accept(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY)
            || $this->isAssignedEmployee($user, $document);
    }

    public function changeStatus(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::SECRETARY);
    }

    private function isAssignedEmployee(User $user, StudentDocument $document): bool
    {
        return $user->hasRole(UserRole::EMPLOYEE)
            && $document->assigned_secretary_user_id === $user->getKey();
    }
}
