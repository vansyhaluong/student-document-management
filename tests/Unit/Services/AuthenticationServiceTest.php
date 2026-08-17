<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use App\Services\ActivityLogService;
use App\Services\AuthenticationService;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_active_user_with_valid_bcrypt_credentials_is_authenticated_and_audited(): void
    {
        $user = $this->user(active: true);
        $users = Mockery::mock(UserRepository::class);
        $activityLog = Mockery::mock(ActivityLogService::class);
        $hasher = Mockery::mock(Hasher::class);
        $database = Mockery::mock(DatabaseManager::class);
        $connection = Mockery::mock(Connection::class);

        $users->shouldReceive('findByUsername')->once()->with('admin.one')->andReturn($user);
        $hasher->shouldReceive('check')->once()->with('correct-password', 'bcrypt-hash')->andReturnTrue();
        $database->shouldReceive('connection')->once()->andReturn($connection);
        $connection->shouldReceive('transaction')->once()->andReturnUsing(
            static fn (callable $callback) => $callback(),
        );
        $users->shouldReceive('save')->once()->with($user)->andReturn($user);
        $activityLog->shouldReceive('recordLogin')->once()->with($user);

        $authenticated = (new AuthenticationService(
            $users,
            $activityLog,
            $hasher,
            $database,
        ))->authenticate('admin.one', 'correct-password');

        $this->assertSame($user, $authenticated);
        $this->assertNotNull($user->last_login_at);
    }

    public function test_unknown_and_inactive_accounts_use_the_same_failed_result(): void
    {
        $users = Mockery::mock(UserRepository::class);
        $activityLog = Mockery::mock(ActivityLogService::class);
        $hasher = Mockery::mock(Hasher::class);
        $database = Mockery::mock(DatabaseManager::class);
        $service = new AuthenticationService($users, $activityLog, $hasher, $database);

        $users->shouldReceive('findByUsername')->once()->with('unknown')->andReturnNull();
        $this->assertNull($service->authenticate('unknown', 'password'));

        $users->shouldReceive('findByUsername')->once()->with('inactive')->andReturn($this->user(active: false));
        $this->assertNull($service->authenticate('inactive', 'password'));

        $hasher->shouldNotReceive('check');
        $activityLog->shouldNotReceive('recordLogin');
    }

    public function test_invalid_password_does_not_update_or_audit_the_user(): void
    {
        $user = $this->user(active: true);
        $users = Mockery::mock(UserRepository::class);
        $activityLog = Mockery::mock(ActivityLogService::class);
        $hasher = Mockery::mock(Hasher::class);
        $database = Mockery::mock(DatabaseManager::class);

        $users->shouldReceive('findByUsername')->once()->andReturn($user);
        $hasher->shouldReceive('check')->once()->andReturnFalse();
        $users->shouldNotReceive('save');
        $activityLog->shouldNotReceive('recordLogin');

        $this->assertNull((new AuthenticationService(
            $users,
            $activityLog,
            $hasher,
            $database,
        ))->authenticate('admin.one', 'wrong-password'));
    }

    private function user(bool $active): User
    {
        return (new User)->forceFill([
            'id' => 1,
            'username' => 'admin.one',
            'password_hash' => 'bcrypt-hash',
            'full_name' => 'Admin kiểm thử',
            'role' => UserRole::ADMIN,
            'is_active' => $active,
        ]);
    }
}
