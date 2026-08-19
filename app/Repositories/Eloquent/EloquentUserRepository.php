<?php

namespace App\Repositories\Eloquent;

use App\DTOs\UserFilterData;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepository
{
    public function findByUsername(string $username): ?User
    {
        return User::query()->where('username', $username)->first();
    }

    public function findById(int $id): ?User
    {
        return $this->safeQuery()->find($id);
    }

    public function lockById(int $id): ?User
    {
        return User::query()->lockForUpdate()->find($id);
    }

    public function paginate(UserFilterData $filters): LengthAwarePaginator
    {
        $query = $this->safeQuery();

        if ($filters->keyword !== null) {
            $keyword = $filters->keyword;
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('username', 'like', "%{$keyword}%")
                    ->orWhere('full_name', 'like', "%{$keyword}%");
            });
        }

        $query->when(
            $filters->role !== null,
            fn (Builder $query) => $query->where('role', $filters->role->value),
        )->when(
            $filters->isActive !== null,
            fn (Builder $query) => $query->where('is_active', $filters->isActive),
        );

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }

    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    public function save(User $user): User
    {
        $user->save();

        return $user->refresh();
    }

    public function allForDocumentFilter(): Collection
    {
        return $this->safeQuery()
            ->orderBy('full_name')
            ->orderBy('id')
            ->get();
    }

    private function safeQuery(): Builder
    {
        return User::query()->select([
            'id',
            'username',
            'full_name',
            'email',
            'role',
            'is_active',
            'last_login_at',
            'created_at',
            'updated_at',
        ]);
    }
}
