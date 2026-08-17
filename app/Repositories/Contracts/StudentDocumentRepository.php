<?php

namespace App\Repositories\Contracts;

use App\DTOs\StudentDocumentFilterData;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StudentDocumentRepository
{
    public function findById(int $id): ?StudentDocument;

    /** @return Collection<int, StudentDocument> */
    public function findPublicByStudentCode(string $studentCode): Collection;

    public function documentCodeExists(string $documentCode): bool;

    public function findVisibleById(int $id, User $actor): ?StudentDocument;

    public function lockById(int $id): ?StudentDocument;

    /** @return LengthAwarePaginator<int, StudentDocument> */
    public function paginate(
        StudentDocumentFilterData $filters,
        User $actor,
    ): LengthAwarePaginator;

    public function save(StudentDocument $document): StudentDocument;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): StudentDocument;
}
