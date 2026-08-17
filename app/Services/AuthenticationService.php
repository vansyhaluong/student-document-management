<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\DatabaseManager;
use SensitiveParameter;

class AuthenticationService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly ActivityLogService $activityLog,
        private readonly Hasher $hasher,
        private readonly DatabaseManager $database,
    ) {}

    public function authenticate(
        string $username,
        #[SensitiveParameter] string $password,
    ): ?User {
        $user = $this->users->findByUsername($username);

        if ($user === null || ! $user->is_active) {
            return null;
        }

        if (! $this->hasher->check($password, $user->password_hash)) {
            return null;
        }

        $this->database->connection()->transaction(function () use ($user): void {
            $user->last_login_at = now();
            $this->users->save($user);
            $this->activityLog->recordLogin($user);
        });

        return $user;
    }

    public function recordLogout(User $user): void
    {
        $this->activityLog->recordLogout($user);
    }
}
