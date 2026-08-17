<?php

namespace Tests\Feature;

use App\DTOs\ReportFilterData;
use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Models\DocumentType;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseFiveDashboardReportsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_aggregates_only_documents_visible_to_an_employee(): void
    {
        $employee = $this->createUser(UserRole::EMPLOYEE, 'phase5.dashboard.employee');
        $otherEmployee = $this->createUser(UserRole::EMPLOYEE, 'phase5.dashboard.other');
        $typeA = $this->createType('P5-DASH-A', 'Xác nhận sinh viên');
        $typeB = $this->createType('P5-DASH-B', 'Bảng điểm');

        $this->createDocument(
            'P5-DASH-OWN-1',
            $employee,
            $typeA,
            StudentDocumentStatus::WAITING_FOR_RECEIPT,
            '2026-01-05 08:00:00',
        );
        $this->createDocument(
            'P5-DASH-OWN-2',
            $employee,
            $typeB,
            StudentDocumentStatus::COMPLETED,
            '2026-01-06 08:00:00',
            '2026-01-10 10:00:00',
        );
        $this->createDocument(
            'P5-DASH-HIDDEN',
            $otherEmployee,
            $typeA,
            StudentDocumentStatus::PROCESSING,
            '2026-01-07 08:00:00',
        );

        $summary = app(DashboardService::class)->summary($employee);
        $statusCounts = $summary['statusOverview']->mapWithKeys(
            static fn (array $item): array => [$item['status']->value => $item['total']],
        );
        $typeCounts = collect($summary['byType'])->mapWithKeys(
            static fn ($item): array => [$item->document_type_name => (int) $item->total],
        );

        $this->assertSame(2, $summary['total']);
        $this->assertSame(1, $statusCounts[StudentDocumentStatus::WAITING_FOR_RECEIPT->value]);
        $this->assertSame(1, $statusCounts[StudentDocumentStatus::COMPLETED->value]);
        $this->assertSame(0, $statusCounts[StudentDocumentStatus::PROCESSING->value]);
        $this->assertSame(1, $typeCounts['Xác nhận sinh viên']);
        $this->assertSame(1, $typeCounts['Bảng điểm']);
        $this->assertCount(2, $summary['recentDocuments']);

        $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('P5-DASH-OWN-1')
            ->assertDontSee('P5-DASH-HIDDEN');
    }

    public function test_secretary_dashboard_scope_matches_all_documents_and_reports_are_role_protected(): void
    {
        $admin = $this->createUser(UserRole::ADMIN, 'phase5.reports.admin');
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase5.reports.secretary');
        $employee = $this->createUser(UserRole::EMPLOYEE, 'phase5.reports.employee');
        $type = $this->createType('P5-SCOPE', 'Giấy xác nhận');
        $this->createDocument('P5-SCOPE-DOC', $employee, $type, StudentDocumentStatus::RECEIVED, '2026-02-02 08:00:00');

        $summary = app(DashboardService::class)->summary($secretary);

        $this->assertSame(StudentDocument::query()->count(), $summary['total']);
        $this->assertSame(
            StudentDocument::query()->count(),
            app(DashboardService::class)->summary($admin)['total'],
        );
        $this->actingAs($secretary)->get(route('reports.index'))->assertOk();
        $this->actingAs($employee)->get(route('reports.index'))->assertForbidden();
    }

    public function test_report_filters_aggregate_status_type_submitted_and_completed_dates(): void
    {
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase5.filters.secretary');
        $responsible = $this->createUser(UserRole::EMPLOYEE, 'phase5.filters.employee');
        $includedType = $this->createType('P5-FILTER-IN', 'Loại cần lọc');
        $otherType = $this->createType('P5-FILTER-OUT', 'Loại khác');

        $this->createDocument(
            'P5-FILTER-INCLUDED',
            $responsible,
            $includedType,
            StudentDocumentStatus::COMPLETED,
            '2026-03-05 08:00:00',
            '2026-03-08 09:00:00',
        );
        $this->createDocument(
            'P5-FLTR-STATUS',
            $responsible,
            $includedType,
            StudentDocumentStatus::PROCESSING,
            '2026-03-06 08:00:00',
        );
        $this->createDocument(
            'P5-FLTR-TYPE',
            $responsible,
            $otherType,
            StudentDocumentStatus::COMPLETED,
            '2026-03-05 08:00:00',
            '2026-03-08 09:00:00',
        );

        $filters = new ReportFilterData(
            documentTypeId: $includedType->id,
            status: StudentDocumentStatus::COMPLETED,
            submittedFrom: '2026-03-01',
            submittedTo: '2026-03-06',
            completedFrom: '2026-03-08',
            completedTo: '2026-03-08',
        );
        $report = app(ReportService::class)->indexData($filters, $secretary)['report'];

        $this->assertSame(1, $report['total']);
        $this->assertSame(StudentDocumentStatus::COMPLETED, $report['byStatus']->first()->status);
        $this->assertSame('Loại cần lọc', $report['byType']->first()->document_type_name);
        $this->assertSame($responsible->full_name, $report['byResponsibleUser']->first()->responsible_user_name);
        $this->assertSame('2026-03-05', $report['submittedByDate']->first()->report_date);
        $this->assertSame('2026-03-08', $report['completedByDate']->first()->report_date);
    }

    public function test_report_rejects_invalid_status_and_invalid_date_ranges(): void
    {
        $secretary = $this->createUser(UserRole::SECRETARY, 'phase5.invalid.secretary');

        $this->actingAs($secretary)
            ->from(route('reports.index'))
            ->get(route('reports.index', [
                'status' => 'unapproved-status',
                'submitted_from' => '2026-04-10',
                'submitted_to' => '2026-04-01',
                'completed_from' => 'not-a-date',
            ]))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors(['status', 'submitted_to', 'completed_from']);
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

    private function createType(string $code, string $name): DocumentType
    {
        return DocumentType::query()->create([
            'code' => $code,
            'name' => $name,
            'description' => null,
            'is_active' => true,
        ]);
    }

    private function createDocument(
        string $code,
        User $responsible,
        DocumentType $type,
        StudentDocumentStatus $status,
        string $submittedAt,
        ?string $completedAt = null,
    ): StudentDocument {
        $studentCode = 'P5-'.substr(md5($code), 0, 10);
        Student::query()->create([
            'student_code' => $studentCode,
            'last_name' => 'Nguyễn',
            'first_name' => $code,
        ]);

        return StudentDocument::query()->create([
            'document_code' => $code,
            'student_code' => $studentCode,
            'document_type_id' => $type->id,
            'status' => $status,
            'assigned_secretary_user_id' => $responsible->id,
            'submitted_at' => Carbon::parse($submittedAt),
            'completed_at' => $completedAt === null ? null : Carbon::parse($completedAt),
            'invalid_reason' => null,
            'note' => null,
        ]);
    }
}
