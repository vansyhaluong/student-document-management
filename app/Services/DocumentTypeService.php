<?php

namespace App\Services;

use App\DTOs\DocumentTypeFilterData;
use App\Models\DocumentType;
use App\Models\User;
use App\Repositories\Contracts\DocumentTypeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentTypeService
{
    public function __construct(
        private readonly DocumentTypeRepository $documentTypes,
        private readonly ActivityLogService $activityLog,
        private readonly DatabaseManager $database,
    ) {}

    /** @return LengthAwarePaginator<int, DocumentType> */
    public function paginate(DocumentTypeFilterData $filters): LengthAwarePaginator
    {
        return $this->documentTypes->paginate($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(DocumentType $documentType): array
    {
        return [
            'id' => $documentType->getKey(),
            'code' => $documentType->code,
            'name' => $documentType->name,
            'description' => $documentType->description,
            'is_active' => $documentType->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): DocumentType
    {
        return $this->database->connection()->transaction(function () use ($data, $actor): DocumentType {
            $documentType = $this->documentTypes->create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'],
            ]);

            $this->activityLog->recordDocumentTypeCreated($actor, $documentType);

            return $documentType;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(DocumentType $target, array $data, User $actor): DocumentType
    {
        return $this->database->connection()->transaction(function () use ($target, $data, $actor): DocumentType {
            $documentType = $this->lockedDocumentType($target->getKey());
            $documentType->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
            $changedFields = array_keys($documentType->getDirty());

            if ($changedFields === []) {
                return $documentType;
            }

            $documentType = $this->documentTypes->save($documentType);
            $this->activityLog->recordDocumentTypeUpdated(
                $actor,
                $documentType,
                $changedFields,
            );

            return $documentType;
        });
    }

    public function toggleStatus(DocumentType $target, User $actor): DocumentType
    {
        return $this->database->connection()->transaction(function () use ($target, $actor): DocumentType {
            $documentType = $this->lockedDocumentType($target->getKey());
            $documentType->is_active = ! $documentType->is_active;
            $documentType = $this->documentTypes->save($documentType);
            $this->activityLog->recordDocumentTypeStatusChanged($actor, $documentType);

            return $documentType;
        });
    }

    private function lockedDocumentType(int $id): DocumentType
    {
        return $this->documentTypes->lockById($id)
            ?? throw (new ModelNotFoundException)->setModel(DocumentType::class, [$id]);
    }
}
