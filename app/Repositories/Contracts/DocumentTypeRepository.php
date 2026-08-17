<?php

namespace App\Repositories\Contracts;

use App\DTOs\DocumentTypeFilterData;
use App\Models\DocumentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DocumentTypeRepository
{
    public function findById(int $id): ?DocumentType;

    public function lockById(int $id): ?DocumentType;

    /** @return Collection<int, DocumentType> */
    public function active(): Collection;

    /** @return Collection<int, DocumentType> */
    public function allOrdered(): Collection;

    /** @return LengthAwarePaginator<int, DocumentType> */
    public function paginate(DocumentTypeFilterData $filters): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): DocumentType;

    public function save(DocumentType $documentType): DocumentType;
}
