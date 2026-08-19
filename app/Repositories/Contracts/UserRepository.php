<?php

namespace App\Repositories\Contracts;

use App\DTOs\UserFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepository
{
    public function findByUsername(string $username): ?User;

    public function findById(int $id): ?User;

    public function lockById(int $id): ?User;

    /** @return LengthAwarePaginator<int, User> */
    public function paginate(UserFilterData $filters): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User;

    public function save(User $user): User;

    /** @return Collection<int, User> */
    public function allForDocumentFilter(): Collection;
}
