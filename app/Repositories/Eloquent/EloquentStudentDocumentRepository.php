<?php

namespace App\Repositories\Eloquent;

use App\DTOs\StudentDocumentFilterData;
use App\Enums\UserRole;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\StudentDocumentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class EloquentStudentDocumentRepository implements StudentDocumentRepository
{
    public function findById(int $id): ?StudentDocument
    {
        return $this->detailQuery()->find($id);
    }

    public function findPublicByStudentCode(string $studentCode): Collection
    {
        return StudentDocument::query()
            ->select([
                'document_code',
                'student_code',
                'document_type_id',
                'status',
                'submitted_at',
                'completed_at',
            ])
            ->with('documentType:id,name')
            ->where('student_code', $studentCode)
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function documentCodeExists(string $documentCode): bool
    {
        return StudentDocument::query()
            ->where('document_code', $documentCode)
            ->exists();
    }

    public function findVisibleById(int $id, User $actor): ?StudentDocument
    {
        return $this->applyVisibilityScope($this->detailQuery(), $actor)->find($id);
    }

    public function lockById(int $id): ?StudentDocument
    {
        return StudentDocument::query()->lockForUpdate()->find($id);
    }

    public function paginate(
        StudentDocumentFilterData $filters,
        User $actor,
    ): LengthAwarePaginator {
        $query = $this->applyVisibilityScope($this->baseQuery(), $actor);

        if ($filters->keyword !== null) {
            $keyword = $filters->keyword;
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('document_code', 'like', "%{$keyword}%")
                    ->orWhere('student_code', 'like', "%{$keyword}%")
                    ->orWhereHas('student', function (Builder $studentQuery) use ($keyword): void {
                        $studentQuery->where('last_name', 'like', "%{$keyword}%")
                            ->orWhere('first_name', 'like', "%{$keyword}%")
                            ->orWhereRaw(
                                "CONCAT(last_name, ' ', first_name) LIKE ?",
                                ["%{$keyword}%"],
                            );
                    });
            });
        }

        $query->when(
            $filters->documentTypeId !== null,
            fn (Builder $query) => $query->where('document_type_id', $filters->documentTypeId),
        )->when(
            $filters->status !== null,
            fn (Builder $query) => $query->where('status', $filters->status->value),
        )->when(
            $filters->responsibleUserId !== null,
            fn (Builder $query) => $query->where(
                'assigned_secretary_user_id',
                $filters->responsibleUserId,
            ),
        )->when(
            $filters->submittedFrom !== null,
            fn (Builder $query) => $query->whereDate(
                'submitted_at',
                '>=',
                $filters->submittedFrom,
            ),
        )->when(
            $filters->submittedTo !== null,
            fn (Builder $query) => $query->whereDate(
                'submitted_at',
                '<=',
                $filters->submittedTo,
            ),
        );

        return $query
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($filters->perPage)
            ->withQueryString();
    }

    public function save(StudentDocument $document): StudentDocument
    {
        $document->save();

        return $document->refresh();
    }

    public function create(array $attributes): StudentDocument
    {
        return StudentDocument::query()->create($attributes)->load([
            'student',
            'documentType',
            'responsibleUser',
        ]);
    }

    private function baseQuery(): Builder
    {
        return StudentDocument::query()->with([
            'student',
            'documentType',
            'responsibleUser',
        ]);
    }

    private function detailQuery(): Builder
    {
        return $this->baseQuery()->with('statusHistory.changedBy');
    }

    private function applyVisibilityScope(Builder $query, User $actor): Builder
    {
        if ($actor->hasRole(UserRole::EMPLOYEE)) {
            $query->where('assigned_secretary_user_id', $actor->getKey());
        }

        return $query;
    }
}
