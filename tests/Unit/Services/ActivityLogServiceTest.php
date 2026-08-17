<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use App\Repositories\Contracts\UserRepository;
use App\Services\ActivityLogService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ActivityLogServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_login_and_logout_audits_contain_only_traceable_allowlisted_data(): void
    {
        $repository = Mockery::mock(ActivityLogRepository::class);
        $user = (new User)->forceFill([
            'id' => 12,
            'username' => 'must-not-be-logged',
            'password_hash' => 'must-not-be-logged',
            'role' => UserRole::ADMIN,
        ]);

        $repository->shouldReceive('create')->once()->with([
            'log_name' => 'authentication',
            'description' => 'Đăng nhập hệ thống',
            'subject_type' => User::class,
            'subject_id' => 12,
            'event' => 'login',
            'causer_type' => User::class,
            'causer_id' => 12,
            'properties' => [],
        ]);
        $repository->shouldReceive('create')->once()->with([
            'log_name' => 'authentication',
            'description' => 'Đăng xuất hệ thống',
            'subject_type' => User::class,
            'subject_id' => 12,
            'event' => 'logout',
            'causer_type' => User::class,
            'causer_id' => 12,
            'properties' => [],
        ]);

        $service = new ActivityLogService($repository, Mockery::mock(UserRepository::class));
        $service->recordLogin($user);
        $service->recordLogout($user);

        $this->addToAssertionCount(1);
    }
}
