<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\StudentDocument;
use App\Models\User;
use App\Policies\DocumentTypePolicy;
use App\Policies\StudentDocumentPolicy;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    public function test_admin_and_secretary_have_approved_document_actions(): void
    {
        $policy = new StudentDocumentPolicy;
        $document = $this->document(30);

        foreach ([$this->user(1, UserRole::ADMIN), $this->user(2, UserRole::SECRETARY)] as $user) {
            $this->assertTrue($policy->view($user, $document));
            $this->assertTrue($policy->create($user));
            $this->assertTrue($policy->update($user, $document));
            $this->assertTrue($policy->assign($user, $document));
            $this->assertTrue($policy->accept($user, $document));
            $this->assertTrue($policy->changeStatus($user, $document));
        }
    }

    public function test_employee_can_only_view_update_and_accept_an_assigned_document(): void
    {
        $policy = new StudentDocumentPolicy;
        $employee = $this->user(30, UserRole::EMPLOYEE);
        $assigned = $this->document(30);
        $unassigned = $this->document(31);

        $this->assertTrue($policy->view($employee, $assigned));
        $this->assertTrue($policy->update($employee, $assigned));
        $this->assertTrue($policy->accept($employee, $assigned));
        $this->assertFalse($policy->view($employee, $unassigned));
        $this->assertFalse($policy->update($employee, $unassigned));
        $this->assertFalse($policy->accept($employee, $unassigned));
        $this->assertFalse($policy->create($employee));
        $this->assertFalse($policy->assign($employee, $assigned));
        $this->assertFalse($policy->changeStatus($employee, $assigned));
    }

    public function test_only_admin_can_manage_users_and_document_types(): void
    {
        $userPolicy = new UserPolicy;
        $typePolicy = new DocumentTypePolicy;
        $admin = $this->user(1, UserRole::ADMIN);
        $secretary = $this->user(2, UserRole::SECRETARY);
        $target = $this->user(3, UserRole::EMPLOYEE);
        $type = new DocumentType;

        $this->assertTrue($userPolicy->viewAny($admin));
        $this->assertTrue($userPolicy->update($admin, $target));
        $this->assertTrue($userPolicy->toggleStatus($admin, $target));
        $this->assertTrue($userPolicy->resetPassword($admin, $target));
        $this->assertTrue($typePolicy->create($admin));
        $this->assertTrue($typePolicy->update($admin, $type));

        $this->assertFalse($userPolicy->viewAny($secretary));
        $this->assertFalse($userPolicy->update($secretary, $target));
        $this->assertFalse($typePolicy->viewAny($secretary));
        $this->assertFalse($typePolicy->update($secretary, $type));
    }

    private function user(int $id, UserRole $role): User
    {
        return (new User)->forceFill([
            'id' => $id,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function document(int $responsibleUserId): StudentDocument
    {
        return (new StudentDocument)->forceFill([
            'id' => 10,
            'assigned_secretary_user_id' => $responsibleUserId,
        ]);
    }
}
