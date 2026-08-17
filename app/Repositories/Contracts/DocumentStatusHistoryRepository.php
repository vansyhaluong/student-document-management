<?php

namespace App\Repositories\Contracts;

use App\Models\DocumentStatusHistory;

interface DocumentStatusHistoryRepository
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): DocumentStatusHistory;
}
