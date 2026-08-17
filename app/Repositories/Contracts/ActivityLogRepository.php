<?php

namespace App\Repositories\Contracts;

use App\DTOs\ActivityLogFilterData;
use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityLogRepository
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): ActivityLog;

    /** @return LengthAwarePaginator<int, ActivityLog> */
    public function paginate(ActivityLogFilterData $filters): LengthAwarePaginator;

    public function findForView(int $id): ?ActivityLog;
}
