<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ReportFilterData;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\ReportRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentReportRepository implements ReportRepository
{
    /** @return array<string, mixed> */
    public function dashboard(User $actor): array
    {
        $query = StudentDocument::query();

        return [
            'total' => (clone $query)->count(),
            'byStatus' => $this->byStatus($query),
            'byType' => $this->byType($query),
            'recentDocuments' => (clone $query)
                ->with(['student', 'documentType'])
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get(),
        ];
    }

    /** @return array<string, mixed> */
    public function report(ReportFilterData $filters, User $actor): array
    {
        $query = $this->applyFilters(StudentDocument::query(), $filters);

        return [
            'total' => (clone $query)->count(),
            'byStatus' => $this->byStatus($query),
            'byType' => $this->byType($query),
            'submittedByDate' => (clone $query)
                ->selectRaw('DATE(submitted_at) as report_date, COUNT(*) as total')
                ->groupByRaw('DATE(submitted_at)')
                ->orderBy('report_date')
                ->get(),
            'completedByDate' => (clone $query)
                ->whereNotNull('completed_at')
                ->selectRaw('DATE(completed_at) as report_date, COUNT(*) as total')
                ->groupByRaw('DATE(completed_at)')
                ->orderBy('report_date')
                ->get(),
        ];
    }

    /** @return Collection<int, StudentDocument> */
    private function byStatus(Builder $query)
    {
        return (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();
    }

    /** @return Collection<int, StudentDocument> */
    private function byType(Builder $query)
    {
        return (clone $query)
            ->join('document_types', 'document_types.id', '=', 'student_documents.document_type_id')
            ->select([
                'document_types.id as document_type_id',
                'document_types.name as document_type_name',
            ])
            ->selectRaw('COUNT(*) as total')
            ->groupBy('document_types.id', 'document_types.name')
            ->orderBy('document_types.name')
            ->get();
    }

    private function applyFilters(Builder $query, ReportFilterData $filters): Builder
    {
        return $query
            ->when(
                $filters->documentTypeId !== null,
                fn (Builder $query) => $query->where('document_type_id', $filters->documentTypeId),
            )
            ->when(
                $filters->status !== null,
                fn (Builder $query) => $query->where('status', $filters->status->value),
            )
            ->when(
                $filters->submittedFrom !== null,
                fn (Builder $query) => $query->whereDate('submitted_at', '>=', $filters->submittedFrom),
            )
            ->when(
                $filters->submittedTo !== null,
                fn (Builder $query) => $query->whereDate('submitted_at', '<=', $filters->submittedTo),
            )
            ->when(
                $filters->completedFrom !== null,
                fn (Builder $query) => $query->whereDate('completed_at', '>=', $filters->completedFrom),
            )
            ->when(
                $filters->completedTo !== null,
                fn (Builder $query) => $query->whereDate('completed_at', '<=', $filters->completedTo),
            );
    }
}
