<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\StudentDocumentRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentDocumentVisibilityTest extends TestCase
{
    public function test_employee_detail_query_is_scoped_to_the_assigned_user(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        app(StudentDocumentRepository::class)->findVisibleById(
            -1,
            $this->user(77, UserRole::EMPLOYEE),
        );

        $query = collect(DB::getQueryLog())->last();

        $this->assertStringContainsString('assigned_secretary_user_id', $query['query']);
        $this->assertContains(77, $query['bindings']);
    }

    public function test_admin_and_secretary_detail_queries_are_not_assignment_scoped(): void
    {
        foreach ([UserRole::ADMIN, UserRole::SECRETARY] as $role) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            app(StudentDocumentRepository::class)->findVisibleById(-1, $this->user(1, $role));

            $query = collect(DB::getQueryLog())->last();

            $this->assertStringNotContainsString('assigned_secretary_user_id', $query['query']);
        }
    }

    private function user(int $id, UserRole $role): User
    {
        return (new User)->forceFill([
            'id' => $id,
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
