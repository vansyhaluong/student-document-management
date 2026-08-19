<?php

namespace App\Services;

use App\DTOs\StudentDocumentFilterData;
use App\Enums\StudentDocumentStatus;
use App\Enums\UserRole;
use App\Exceptions\BusinessRuleException;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Repositories\Contracts\DocumentStatusHistoryRepository;
use App\Repositories\Contracts\DocumentTypeRepository;
use App\Repositories\Contracts\StudentDocumentRepository;
use App\Repositories\Contracts\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StudentDocumentService
{
    private const PUBLIC_DOCUMENT_CODE_ATTEMPTS = 20;

    public function __construct(
        private readonly StudentDocumentRepository $documents,
        private readonly StudentRepository $students,
        private readonly DocumentTypeRepository $documentTypes,
        private readonly DocumentStatusHistoryRepository $history,
        private readonly ActivityLogService $activityLog,
    ) {}

    public function paginate(StudentDocumentFilterData $filters, User $actor): LengthAwarePaginator
    {
        return $this->documents->paginate($filters, $actor);
    }

    public function findVisible(int $id, User $actor): StudentDocument
    {
        $document = $this->documents->findVisibleById($id, $actor);

        if ($document === null) {
            throw (new ModelNotFoundException)->setModel(StudentDocument::class, [$id]);
        }

        return $document;
    }

    /** @return array<string, mixed> */
    public function indexData(StudentDocumentFilterData $filters, User $actor): array
    {
        return [
            'documents' => $this->paginate($filters, $actor),
            'documentTypes' => $this->documentTypes->allOrdered(),
            'statuses' => StudentDocumentStatus::cases(),
        ];
    }

    /** @return array<string, mixed> */
    public function formData(?StudentDocument $document = null): array
    {
        $types = $this->documentTypes->active();

        if ($document !== null && ! $types->contains('id', $document->document_type_id)) {
            $currentType = $this->documentTypes->findById($document->document_type_id);
            if ($currentType !== null) {
                $types->push($currentType);
            }
        }

        return [
            'document' => $document,
            'documentTypes' => $types->sortBy('name')->values(),
        ];
    }

    /** @return array{documentTypes: array<int, array{id: int, name: string}>} */
    public function publicHomeData(): array
    {
        return [
            'documentTypes' => $this->documentTypes->active()
                ->map(fn ($type): array => [
                    'id' => (int) $type->getKey(),
                    'name' => $type->name,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{studentExists: bool, lookupStudentName: ?string, lookupResults: array<int, array{
     *     document_code: string,
     *     document_type: string,
     *     status: string,
     *     submitted_at: string,
     *     completed_at: ?string,
     *     notes: ?string
     * }>}
     */
    public function publicLookup(string $studentCode): array
    {
        $lookup = $this->findPublicDocumentsByStudentCode($studentCode);

        return [
            'studentExists' => $lookup['studentExists'],
            'lookupStudentName' => $lookup['studentName'],
            'lookupResults' => $lookup['documents']
                ->map(fn (StudentDocument $document): array => $this->mapPublicDocumentForWeb($document))
                ->all(),
        ];
    }

    /**
     * @return array{
     *     student_code: string,
     *     student_exists: bool,
     *     documents: array<int, array{
     *         document_code: string,
     *         document_type: string,
     *         status: string,
     *         status_label: string,
     *         submitted_at: string,
     *         completed_at: ?string,
     *         notes: ?string
     *     }>
     * }
     */
    public function publicDocumentsForApi(string $studentCode): array
    {
        $lookup = $this->findPublicDocumentsByStudentCode($studentCode);

        return [
            'student_code' => $studentCode,
            'student_exists' => $lookup['studentExists'],
            'documents' => $lookup['documents']
                ->map(fn (StudentDocument $document): array => $this->mapPublicDocumentForApi($document))
                ->all(),
        ];
    }

    /** @param array{student_code: string, document_type_id: int|string} $data */
    public function createPublic(array $data): StudentDocument
    {
        for ($attempt = 0; $attempt < self::PUBLIC_DOCUMENT_CODE_ATTEMPTS; $attempt++) {
            try {
                return DB::transaction(function () use ($data): StudentDocument {
                    $student = $this->requireStudent($data['student_code']);
                    $this->assertActiveDocumentType((int) $data['document_type_id']);
                    $submittedAt = now();

                    $document = $this->documents->create([
                        'document_code' => $this->generatePublicDocumentCode(
                            $submittedAt->format('ym'),
                        ),
                        'student_code' => $student->student_code,
                        'document_type_id' => (int) $data['document_type_id'],
                        'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT,
                        'submitted_at' => $submittedAt,
                        'completed_at' => null,
                        'invalid_reason' => null,
                        'note' => null,
                    ]);

                    // Approved public-only exception: no initial history because there is no user actor.
                    $this->activityLog->recordPublicStudentDocumentCreated($document);

                    return $document;
                });
            } catch (QueryException $exception) {
                if ((int) ($exception->errorInfo[1] ?? 0) !== 1062) {
                    throw $exception;
                }
            }
        }

        throw new BusinessRuleException('Không thể tạo mã hồ sơ duy nhất. Vui lòng thử lại.');
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): StudentDocument
    {
        return DB::transaction(function () use ($data, $actor): StudentDocument {
            $student = $this->requireStudent($data['student_code']);
            $this->assertActiveDocumentType((int) $data['document_type_id']);

            $document = $this->documents->create([
                'document_code' => $data['document_code'],
                'student_code' => $student->student_code,
                'document_type_id' => (int) $data['document_type_id'],
                'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT,
                'submitted_at' => now(),
                'completed_at' => null,
                'invalid_reason' => null,
                'note' => $data['note'] ?? null,
            ]);

            $this->history->create([
                'student_document_id' => $document->getKey(),
                'status' => StudentDocumentStatus::WAITING_FOR_RECEIPT->value,
                'invalid_reason' => null,
                'note' => $data['note'] ?? null,
                'changed_by_user_id' => $actor->getKey(),
                'changed_at' => now(),
            ]);
            $this->activityLog->recordStudentDocumentCreated($actor, $document);

            return $document;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, User $actor): StudentDocument
    {
        return DB::transaction(function () use ($id, $data, $actor): StudentDocument {
            $document = $this->locked($id);
            Gate::forUser($actor)->authorize('update', $document);

            $allowed = $actor->hasRole(UserRole::EMPLOYEE)
                ? ['note']
                : ['student_code', 'document_type_id', 'note'];
            $attributes = collect($data)->only($allowed)->all();

            if (array_key_exists('student_code', $attributes)) {
                $attributes['student_code'] = $this->requireStudent($attributes['student_code'])->student_code;
            }

            if (array_key_exists('document_type_id', $attributes)
                && (int) $attributes['document_type_id'] !== $document->document_type_id) {
                $this->assertActiveDocumentType((int) $attributes['document_type_id']);
            }

            $document->fill($attributes);
            $changedFields = array_keys($document->getDirty());

            if ($changedFields !== []) {
                $document = $this->documents->save($document);
                $this->activityLog->recordStudentDocumentUpdated($actor, $document, $changedFields);
            }

            return $document;
        });
    }

    public function changeStatus(
        int $id,
        StudentDocumentStatus $nextStatus,
        ?string $note,
        ?string $invalidReason,
        User $actor,
    ): StudentDocument {
        return DB::transaction(function () use (
            $id,
            $nextStatus,
            $note,
            $invalidReason,
            $actor,
        ): StudentDocument {
            $document = $this->locked($id);
            Gate::forUser($actor)->authorize('changeStatus', $document);

            return $this->transitionLocked(
                $document,
                $nextStatus,
                $note,
                $invalidReason,
                $actor,
            );
        });
    }

    private function transitionLocked(
        StudentDocument $document,
        StudentDocumentStatus $nextStatus,
        ?string $note,
        ?string $invalidReason,
        User $actor,
    ): StudentDocument {
        $currentStatus = $document->status;

        if (! $currentStatus->canTransitionTo($nextStatus)) {
            throw new BusinessRuleException('Không thể chuyển hồ sơ sang trạng thái đã chọn.');
        }

        if ($nextStatus === StudentDocumentStatus::INVALID && blank($invalidReason)) {
            throw new BusinessRuleException(
                'Lý do không hợp lệ là bắt buộc.',
                ['invalid_reason' => 'Vui lòng nhập lý do hồ sơ không hợp lệ.'],
            );
        }

        $document->status = $nextStatus;
        $document->completed_at = $nextStatus === StudentDocumentStatus::COMPLETED ? now() : null;
        $document->invalid_reason = $nextStatus === StudentDocumentStatus::INVALID
            ? $invalidReason
            : null;
        if (filled($note)) {
            $document->note = $note;
        }
        $document = $this->documents->save($document);

        $this->history->create([
            'student_document_id' => $document->getKey(),
            'status' => $nextStatus->value,
            'invalid_reason' => $document->invalid_reason,
            'note' => $note,
            'changed_by_user_id' => $actor->getKey(),
            'changed_at' => now(),
        ]);
        $this->activityLog->recordStudentDocumentStatusChanged(
            $actor,
            $document,
            $currentStatus->value,
        );

        return $document;
    }

    private function locked(int $id): StudentDocument
    {
        $document = $this->documents->lockById($id);

        if ($document === null) {
            throw (new ModelNotFoundException)->setModel(StudentDocument::class, [$id]);
        }

        return $document;
    }

    private function requireStudent(string $studentCode): Student
    {
        $student = $this->students->findByCode($studentCode);

        if ($student === null) {
            throw new BusinessRuleException(
                'Sinh viên không tồn tại.',
                ['student_code' => 'Mã sinh viên không tồn tại trong hệ thống.'],
            );
        }

        return $student;
    }

    /**
     * @return array{studentExists: bool, studentName: ?string, documents: Collection<int, StudentDocument>}
     */
    private function findPublicDocumentsByStudentCode(string $studentCode): array
    {
        $student = $this->students->findByCode($studentCode);

        if ($student === null) {
            return [
                'studentExists' => false,
                'studentName' => null,
                'documents' => collect(),
            ];
        }

        return [
            'studentExists' => true,
            'studentName' => $student->full_name,
            'documents' => $this->documents->findPublicByStudentCode($student->student_code),
        ];
    }

    /**
     * @return array{
     *     document_code: string,
     *     document_type: string,
     *     status: string,
     *     submitted_at: string,
     *     completed_at: ?string,
     *     notes: ?string
     * }
     */
    private function mapPublicDocumentForWeb(StudentDocument $document): array
    {
        return [
            'document_code' => $document->document_code,
            'document_type' => $this->publicDocumentTypeName($document),
            'status' => $document->status->label(),
            'submitted_at' => $document->submitted_at->format('d/m/Y'),
            'completed_at' => $document->completed_at?->format('d/m/Y'),
            'notes' => $this->publicNotes($document),
        ];
    }

    /**
     * @return array{
     *     document_code: string,
     *     document_type: string,
     *     status: string,
     *     status_label: string,
     *     submitted_at: string,
     *     completed_at: ?string,
     *     notes: ?string
     * }
     */
    private function mapPublicDocumentForApi(StudentDocument $document): array
    {
        return [
            'document_code' => $document->document_code,
            'document_type' => $this->publicDocumentTypeName($document),
            'status' => $document->status->value,
            'status_label' => $document->status->label(),
            'submitted_at' => $document->submitted_at->format('Y-m-d'),
            'completed_at' => $document->completed_at?->format('Y-m-d'),
            'notes' => $this->publicNotes($document),
        ];
    }

    private function publicNotes(StudentDocument $document): ?string
    {
        $documentNote = trim((string) $document->note);

        if ($documentNote !== '') {
            return $documentNote;
        }

        if (! $document->relationLoaded('statusHistory')) {
            return null;
        }

        $latestHistoryNote = $document->statusHistory
            ->sortByDesc(fn ($entry): array => [
                $entry->changed_at?->timestamp ?? 0,
                (int) $entry->getKey(),
            ])
            ->map(fn ($entry): string => trim((string) $entry->note))
            ->first(fn (string $note): bool => $note !== '');

        return $latestHistoryNote !== null && $latestHistoryNote !== ''
            ? $latestHistoryNote
            : null;
    }

    private function publicDocumentTypeName(StudentDocument $document): string
    {
        return $document->documentType?->name ?? 'Không xác định';
    }

    private function assertActiveDocumentType(int $documentTypeId): void
    {
        $type = $this->documentTypes->findById($documentTypeId);

        if ($type === null || ! $type->is_active) {
            throw new BusinessRuleException(
                'Loại hồ sơ không khả dụng.',
                ['document_type_id' => 'Vui lòng chọn loại hồ sơ đang sử dụng.'],
            );
        }
    }

    private function generatePublicDocumentCode(string $yearMonth): string
    {
        do {
            $code = 'HS'.$yearMonth.str_pad(
                (string) random_int(0, 999999),
                6,
                '0',
                STR_PAD_LEFT,
            );
        } while ($this->documents->documentCodeExists($code));

        return $code;
    }
}
