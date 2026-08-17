<?php

namespace App\Services;

use App\DTOs\UserFilterData;
use App\Enums\UserRole;
use App\Exceptions\BusinessRuleException;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use SensitiveParameter;

class UserService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly ActivityLogService $activityLog,
        private readonly Hasher $hasher,
        private readonly DatabaseManager $database,
    ) {}

    /** @return LengthAwarePaginator<int, User> */
    public function paginate(UserFilterData $filters): LengthAwarePaginator
    {
        return $this->users->paginate($filters);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function roles(): array
    {
        return array_map(
            static fn (UserRole $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            UserRole::cases(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(User $user): array
    {
        return [
            'id' => $user->getKey(),
            'username' => $user->username,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): User
    {
        return $this->database->connection()->transaction(function () use ($data, $actor): User {
            $user = $this->users->create([
                'username' => $data['username'],
                'password_hash' => $this->hasher->make($data['password']),
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'role' => $data['role'],
                'is_active' => $data['is_active'],
            ]);

            $this->activityLog->recordUserCreated($actor, $user);

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $target, array $data, User $actor): User
    {
        return $this->database->connection()->transaction(function () use ($target, $data, $actor): User {
            $user = $this->lockedUser($target->getKey());
            $nextRole = UserRole::from($data['role']);

            if ($actor->is($user) && $nextRole !== UserRole::ADMIN) {
                throw new BusinessRuleException(
                    'Không thể thay đổi vai trò của chính bạn.',
                    ['role' => ['Admin không được tự đổi sang vai trò khác.']],
                );
            }

            $previousRole = $user->role->value;
            $user->fill([
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'role' => $nextRole,
            ]);
            $changedFields = array_keys($user->getDirty());

            if ($changedFields === []) {
                return $user;
            }

            $user = $this->users->save($user);
            $this->activityLog->recordUserUpdated(
                $actor,
                $user,
                $changedFields,
                $previousRole,
            );

            return $user;
        });
    }

    public function toggleStatus(User $target, User $actor): User
    {
        return $this->database->connection()->transaction(function () use ($target, $actor): User {
            $user = $this->lockedUser($target->getKey());

            if ($actor->is($user) && $user->is_active) {
                throw new BusinessRuleException(
                    'Không thể khóa tài khoản của chính bạn.',
                    ['status' => ['Admin không được tự khóa tài khoản đang sử dụng.']],
                );
            }

            $user->is_active = ! $user->is_active;
            $user = $this->users->save($user);
            $this->activityLog->recordUserStatusChanged($actor, $user);

            return $user;
        });
    }

    public function resetPassword(
        User $target,
        #[SensitiveParameter] string $password,
        User $actor,
    ): void {
        $this->database->connection()->transaction(function () use ($target, $password, $actor): void {
            $user = $this->lockedUser($target->getKey());
            $user->password_hash = $this->hasher->make($password);
            $user = $this->users->save($user);
            $this->activityLog->recordUserPasswordReset($actor, $user);
        });
    }

    private function lockedUser(int $id): User
    {
        return $this->users->lockById($id)
            ?? throw (new ModelNotFoundException)->setModel(User::class, [$id]);
    }
}
