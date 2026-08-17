<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseFiveAuditLogTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_filter_and_view_safe_audit_details(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase5.audit.admin');
        $actor = $this->createUser(UserRole::SECRETARY, 'phase5.audit.actor');
        $included = $this->createAudit(
            $actor,
            'user.created',
            User::class,
            $actor->id,
            [
                'role' => UserRole::SECRETARY->value,
                'password' => 'must-not-appear',
                'token' => 'must-not-appear-token',
            ],
        );
        $this->createAudit($admin, 'student_document.created', StudentDocument::class, 9999, []);

        $response = $this->actingAs($admin)->get(route('activity-log.index', [
            'event' => 'user.created',
            'actor_user_id' => $actor->id,
            'subject_type' => User::class,
            'subject_id' => $actor->id,
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response
            ->assertOk()
            ->assertSee('Tạo tài khoản người dùng')
            ->assertSee($actor->full_name)
            ->assertDontSee('student_document.created');

        $this->actingAs($admin)
            ->get(route('activity-log.show', $included->id))
            ->assertOk()
            ->assertSee('secretary')
            ->assertDontSee('must-not-appear')
            ->assertDontSee('must-not-appear-token');
    }

    public function test_secretary_and_employee_cannot_access_audit_routes(): void
    {
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase5.audit.secretary');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'phase5.audit.employee');
        $audit = $this->createAudit($secretary, 'login', User::class, $secretary->id, []);

        $this->actingAs($secretary)->get(route('activity-log.index'))->assertForbidden();
        $this->actingAs($secretary)->get(route('activity-log.show', $audit->id))->assertForbidden();
        $this->actingAs($employee)->get(route('activity-log.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('activity-log.show', $audit->id))->assertForbidden();
    }

    public function test_audit_module_has_no_edit_or_delete_application_routes(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase5.audit.readonly');
        $audit = $this->createAudit($admin, 'logout', User::class, $admin->id, []);

        $this->actingAs($admin)
            ->patch('/activity-log/'.$audit->id)
            ->assertMethodNotAllowed();
        $this->actingAs($admin)
            ->delete('/activity-log/'.$audit->id)
            ->assertMethodNotAllowed();
    }

    private function createUser(UserRole $role, string $username): User
    {
        return User::query()->create([
            'username' => $username,
            'password_hash' => Hash::make('test-password'),
            'full_name' => $username,
            'email' => null,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    /** @param array<string, mixed> $properties */
    private function createAudit(
        User $actor,
        string $event,
        string $subjectType,
        int $subjectId,
        array $properties,
    ): ActivityLog {
        return ActivityLog::query()->create([
            'log_name' => 'business',
            'description' => 'Không hiển thị mô tả thô',
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'event' => $event,
            'causer_type' => User::class,
            'causer_id' => $actor->id,
            'properties' => $properties,
        ]);
    }
}
