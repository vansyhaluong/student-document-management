<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_guest_can_view_login_and_is_redirected_from_dashboard(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Đăng nhập hệ thống')
            ->assertSee('images/tdc-logo.png');

        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_login_route_is_rate_limited(): void
    {
        $route = Route::getRoutes()->getByName('login.store');

        $this->assertInstanceOf(LaravelRoute::class, $route);
        $this->assertContains('throttle:5,1', $route->gatherMiddleware());
    }

    public function test_valid_credentials_create_an_authenticated_session(): void
    {
        $user = $this->user(UserRole::SECRETARY);

        $this->mock(AuthenticationService::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('authenticate')
                ->once()
                ->with('secretary.one', 'correct-password')
                ->andReturn($user);
        });

        $response = $this->post('/login', [
            'username' => ' secretary.one ',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_use_a_generic_error_and_do_not_flash_password(): void
    {
        $this->mock(AuthenticationService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('authenticate')->once()->andReturnNull();
        });

        $response = $this->from('/login')->post('/login', [
            'username' => 'unknown.user',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'username' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ]);

        $this->assertSame('unknown.user', session()->getOldInput('username'));
        $this->assertNull(session()->getOldInput('password'));
        $this->assertGuest();
    }

    public function test_login_request_validates_required_fields(): void
    {
        $response = $this->from('/login')->post('/login', []);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['username', 'password']);
    }

    public function test_authenticated_user_can_logout_securely(): void
    {
        $user = $this->user(UserRole::ADMIN);

        $this->mock(AuthenticationService::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('recordLogout')->once()->with($user);
        });

        $response = $this->actingAs($user)->post('/logout');

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Đã đăng xuất khỏi hệ thống.');
        $this->assertGuest();
    }

    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $user = $this->user(UserRole::EMPLOYEE, false);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'username' => 'Tài khoản đã bị khóa.',
            ]);

        $this->assertGuest();
    }

    public function test_role_middleware_rejects_an_unapproved_role(): void
    {
        Route::middleware(['web', 'auth', 'active', 'role:admin'])
            ->get('/testing/admin-only', fn () => 'allowed');

        $this->actingAs($this->user(UserRole::EMPLOYEE))
            ->get('/testing/admin-only')
            ->assertForbidden();

        $this->actingAs($this->user(UserRole::ADMIN))
            ->get('/testing/admin-only')
            ->assertOk()
            ->assertSee('allowed');
    }

    public function test_dashboard_renders_the_authenticated_application_shell(): void
    {
        $this->actingAs($this->user(UserRole::SECRETARY))
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Không gian làm việc')
            ->assertSee('Báo cáo')
            ->assertDontSee('Người dùng');
    }

    public function test_application_navigation_is_presented_for_each_approved_role(): void
    {
        $this->actingAs($this->user(UserRole::ADMIN))
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Loại hồ sơ')
            ->assertSee('Người dùng')
            ->assertSee('Báo cáo')
            ->assertSee('Nhật ký hoạt động');

        $this->actingAs($this->user(UserRole::SECRETARY))
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Loại hồ sơ')
            ->assertDontSee('Người dùng')
            ->assertSee('Báo cáo')
            ->assertDontSee('Nhật ký hoạt động');

        $this->actingAs($this->user(UserRole::EMPLOYEE))
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Loại hồ sơ')
            ->assertDontSee('Người dùng')
            ->assertDontSee('Báo cáo')
            ->assertDontSee('Nhật ký hoạt động');
    }

    private function user(UserRole $role, bool $active = true): User
    {
        return (new User)->forceFill([
            'id' => match ($role) {
                UserRole::ADMIN => 101,
                UserRole::SECRETARY => 102,
                UserRole::EMPLOYEE => 103,
            },
            'username' => strtolower($role->name).'.one',
            'full_name' => $role->label().' kiểm thử',
            'role' => $role,
            'is_active' => $active,
        ]);
    }
}
