<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\User;

class DocumentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function view(User $user, DocumentType $documentType): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }

    public function update(User $user, DocumentType $documentType): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
