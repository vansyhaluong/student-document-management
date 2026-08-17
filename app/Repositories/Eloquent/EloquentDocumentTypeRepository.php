<?php

namespace App\Repositories\Eloquent;

use App\DTOs\DocumentTypeFilterData;
use App\Models\DocumentType;
use App\Repositories\Contracts\DocumentTypeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentDocumentTypeRepository implements DocumentTypeRepository
{
    public function findById(int $id): ?DocumentType
    {
        return DocumentType::query()->find($id);
    }

    public function lockById(int $id): ?DocumentType
    {
        return DocumentType::query()->lockForUpdate()->find($id);
    }

    public function active(): Collection
    {
        return DocumentType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function allOrdered(): Collection
    {
        return DocumentType::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }

    public function paginate(DocumentTypeFilterData $filters): LengthAwarePaginator
    {
        $query = DocumentType::query();

        if ($filters->keyword !== null) {
            $keyword = $filters->keyword;
            $query->where(function (Builder $query) use ($keyword): void {
                $query->where('code', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $query->when(
            $filters->isActive !== null,
            fn (Builder $query) => $query->where('is_active', $filters->isActive),
        );

        return $query
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($filters->perPage)
            ->withQueryString();
    }

    public function create(array $attributes): DocumentType
    {
        return DocumentType::query()->create($attributes);
    }

    public function save(DocumentType $documentType): DocumentType
    {
        $documentType->save();

        return $documentType->refresh();
    }
}
