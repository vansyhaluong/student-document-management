<?php

namespace App\Repositories\Eloquent;

use App\Models\DocumentStatus;
use App\Repositories\Contracts\DocumentStatusRepository;
use Illuminate\Support\Collection;

class EloquentDocumentStatusRepository implements DocumentStatusRepository
{
    public function findByCode(string $code): ?DocumentStatus
    {
        return DocumentStatus::query()->where('code', $code)->first();
    }

    public function activeOrdered(): Collection
    {
        return DocumentStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
