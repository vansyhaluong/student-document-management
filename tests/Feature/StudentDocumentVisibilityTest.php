<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\StudentDocumentRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentDocumentVisibilityTest extends TestCase
{
    public function test_no_role_scopes_document_queries_by_assigned_user(): void
    {
        foreach ([UserRole::ADMIN, UserRole::SECRETARY, UserRole::EMPLOYEE] as $role) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            app(StudentDocumentRepository::class)->findVisibleById(-1, $this->user(77, $role));

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
