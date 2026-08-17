<?php

namespace App\Repositories\Contracts;

use App\Models\DocumentStatus;
use Illuminate\Support\Collection;

interface DocumentStatusRepository
{
    public function findByCode(string $code): ?DocumentStatus;

    /** @return Collection<int, DocumentStatus> */
    public function activeOrdered(): Collection;
}
