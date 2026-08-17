<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class DocumentTypeManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_admin_can_access_document_type_management(): void
    {
        $this->actingAs($this->createUser(UserRole::SECRETARY, 'secretary.types'))
            ->get('/document-types')
            ->assertForbidden();

        $this->actingAs($this->createUser(UserRole::EMPLOYEE, 'employee.types'))
            ->get('/document-types/create')
            ->assertForbidden();
    }

    public function test_admin_can_list_and_filter_document_types(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.list');
        DocumentType::query()->create([
            'code' => 'SEARCH-TYPE',
            'name' => 'Loại cần tìm',
            'description' => null,
            'is_active' => true,
        ]);
        DocumentType::query()->create([
            'code' => 'HIDDEN-TYPE',
            'name' => 'Loại khác',
            'description' => null,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get('/document-types?keyword=SEARCH-TYPE&is_active=1')
            ->assertOk()
            ->assertSee('SEARCH-TYPE')
            ->assertDontSee('HIDDEN-TYPE');
    }

    public function test_admin_can_create_unique_document_type(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.create');
        $this->expectAudit('document_type.created');

        $this->actingAs($admin)
            ->post('/document-types', [
                'code' => 'NEW-TYPE',
                'name' => 'Loại hồ sơ mới',
                'description' => 'Mô tả kiểm thử',
                'is_active' => '1',
            ])
            ->assertRedirect(route('document-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('document_types', [
            'code' => 'NEW-TYPE',
            'name' => 'Loại hồ sơ mới',
            'is_active' => 1,
        ]);
    }

    public function test_document_type_code_must_be_unique(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.validation');
        DocumentType::query()->create([
            'code' => 'EXISTING',
            'name' => 'Loại hiện có',
            'description' => null,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('document-types.create'))
            ->post('/document-types', [
                'code' => 'EXISTING',
                'name' => 'Trùng mã',
                'is_active' => '1',
            ])
            ->assertRedirect(route('document-types.create'))
            ->assertSessionHasErrors('code');
    }

    public function test_document_type_code_is_immutable_while_name_and_description_can_change(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.update');
        $type = DocumentType::query()->create([
            'code' => 'IMMUTABLE',
            'name' => 'Tên cũ',
            'description' => null,
            'is_active' => true,
        ]);
        $this->expectAudit('document_type.updated');

        $this->actingAs($admin)
            ->put(route('document-types.update', $type), [
                'code' => 'CHANGED',
                'name' => 'Tên mới',
                'description' => 'Mô tả mới',
            ])
            ->assertRedirect(route('document-types.index'));

        $type->refresh();
        $this->assertSame('IMMUTABLE', $type->code);
        $this->assertSame('Tên mới', $type->name);
        $this->assertSame('Mô tả mới', $type->description);
    }

    public function test_admin_can_toggle_document_type_without_delete_route(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.status');
        $type = DocumentType::query()->create([
            'code' => 'TOGGLE',
            'name' => 'Loại bật tắt',
            'description' => null,
            'is_active' => true,
        ]);
        $this->expectAudit('document_type.deactivated');

        $this->actingAs($admin)
            ->patch(route('document-types.status', $type))
            ->assertSessionHas('success');

        $this->assertFalse($type->fresh()->is_active);
        $this->assertNull(app('router')->getRoutes()->getByAction(
            'App\\Http\\Controllers\\DocumentTypeController@destroy',
        ));
    }

    public function test_document_type_creation_rolls_back_when_audit_write_fails(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'admin.types.rollback');
        $this->mock(ActivityLogRepository::class)
            ->shouldReceive('create')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post('/document-types', [
                'code' => 'ROLLBACK-TYPE',
                'name' => 'Không được lưu',
                'description' => null,
                'is_active' => '1',
            ]);

            $this->fail('Expected the audit exception to be thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseMissing('document_types', ['code' => 'ROLLBACK-TYPE']);
    }

    private function createUser(UserRole $role, string $username): User
    {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => 'Người dùng kiểm thử',
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
            ->with(Mockery::on(
                static fn (array $attributes): bool => $attributes['event'] === $event,
            ));
    }
}
