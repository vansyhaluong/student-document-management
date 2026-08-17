<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_admin_can_access_user_management(): void
    {
        $this->actingAs($this->createUser(UserRole::SECRETARY, 'secretary.access'))
            ->get('/users')
            ->assertForbidden();

        $this->actingAs($this->createUser(UserRole::EMPLOYEE, 'employee.access'))
            ->get('/users/create')
            ->assertForbidden();
    }

    public function test_admin_can_list_and_search_users_without_exposing_password_hashes(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.list');
        $this->createUser(UserRole::EMPLOYEE, 'search.target', 'Người cần tìm');
        $this->createUser(UserRole::EMPLOYEE, 'hidden.target', 'Người khác');

        $response = $this->actingAs($admin)->get('/users?keyword=search.target');

        $response
            ->assertOk()
            ->assertSee('search.target')
            ->assertDontSee('hidden.target')
            ->assertDontSee('test-password-hash');
    }

    public function test_admin_can_create_user_with_confirmed_hashed_password(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.create');
        $this->expectAudit('user.created');

        $response = $this->actingAs($admin)->post('/users', [
            'username' => 'new.employee',
            'full_name' => 'Nhân viên mới',
            'email' => null,
            'role' => UserRole::EMPLOYEE->value,
            'is_active' => '1',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $response
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $created = User::query()->where('username', 'new.employee')->firstOrFail();

        $this->assertSame(UserRole::EMPLOYEE, $created->role);
        $this->assertTrue($created->is_active);
        $this->assertNotSame('password-123', $created->password_hash);
        $this->assertTrue(Hash::check('password-123', $created->password_hash));
    }

    public function test_create_user_requires_unique_username_approved_role_and_password_confirmation(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.validation');
        $this->createUser(UserRole::EMPLOYEE, 'existing.username');

        $this->actingAs($admin)
            ->from('/users/create')
            ->post('/users', [
                'username' => 'existing.username',
                'full_name' => 'Dữ liệu lỗi',
                'role' => 'unapproved-role',
                'is_active' => '1',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect('/users/create')
            ->assertSessionHasErrors(['username', 'role', 'password']);
    }

    public function test_username_is_immutable_while_admin_can_update_allowed_fields(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.update');
        $target = $this->createUser(UserRole::EMPLOYEE, 'immutable.username');
        $this->expectAudit('user.role_changed');

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'username' => 'changed.username',
                'full_name' => 'Tên đã cập nhật',
                'email' => 'updated@example.test',
                'role' => UserRole::SECRETARY->value,
            ])
            ->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertSame('immutable.username', $target->username);
        $this->assertSame('Tên đã cập nhật', $target->full_name);
        $this->assertSame(UserRole::SECRETARY, $target->role);
    }

    public function test_admin_cannot_demote_or_lock_their_own_account(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.self');

        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'full_name' => $admin->full_name,
                'email' => null,
                'role' => UserRole::EMPLOYEE->value,
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->from(route('users.index'))
            ->patch(route('users.status', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors('status');

        $admin->refresh();
        $this->assertSame(UserRole::ADMIN, $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_can_update_their_own_non_privileged_information(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.profile');
        $this->expectAudit('user.updated');

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'full_name' => 'Admin đã cập nhật',
                'email' => null,
                'role' => UserRole::ADMIN->value,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame('Admin đã cập nhật', $admin->fresh()->full_name);
        $this->assertSame(UserRole::ADMIN, $admin->fresh()->role);
    }

    public function test_admin_can_lock_and_unlock_another_user(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.status');
        $target = $this->createUser(UserRole::EMPLOYEE, 'employee.status');

        $this->expectAudit('user.locked');
        $this->actingAs($admin)->patch(route('users.status', $target))->assertSessionHas('success');
        $this->assertFalse($target->fresh()->is_active);

        $this->expectAudit('user.unlocked');
        $this->actingAs($admin)->patch(route('users.status', $target))->assertSessionHas('success');
        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_can_reset_password_without_flashing_or_auditing_password_value(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.password');
        $target = $this->createUser(UserRole::EMPLOYEE, 'employee.password');
        $this->expectAudit('user.password_reset');

        $response = $this->actingAs($admin)
            ->from(route('users.edit', $target))
            ->put(route('users.password', $target), [
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ]);

        $response
            ->assertRedirect(route('users.edit', $target))
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-password-123', $target->fresh()->password_hash));
        $this->assertNull(session()->getOldInput('password'));
        $this->assertNull(session()->getOldInput('password_confirmation'));
    }

    public function test_user_creation_rolls_back_when_audit_write_fails(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.rollback');
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post('/users', [
                'username' => 'rolled.back.user',
                'full_name' => 'Rollback User',
                'email' => null,
                'role' => UserRole::EMPLOYEE->value,
                'is_active' => '1',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
            ]);

            $this->fail('Expected the audit exception to be thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseMissing('users', ['username' => 'rolled.back.user']);
    }

    private function createUser(
        UserRole $role,
        string $username,
        string $fullName = 'Người dùng kiểm thử',
    ): User {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => $fullName,
            'email' => null,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function expectAudit(string $event): void
    {
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $attributes) use ($event): bool {
                $encoded = json_encode($attributes);

                return $attributes['event'] === $event
                    && ! str_contains($encoded, 'password-123')
                    && ! str_contains($encoded, 'new-password-123');
            }));
    }
}
