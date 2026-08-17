<?php

namespace App\Repositories\Eloquent;

use App\Models\DocumentStatusHistory;
use App\Repositories\Contracts\DocumentStatusHistoryRepository;

class EloquentDocumentStatusHistoryRepository implements DocumentStatusHistoryRepository
{
    public function create(array $attributes): DocumentStatusHistory
    {
        return DocumentStatusHistory::query()->create($attributes);
    }
}
