<?php

namespace App\Repositories\Contracts;

use App\DTOs\ReportFilterData;
use App\Models\User;

interface ReportRepository
{
    /** @return array<string, mixed> */
    public function dashboard(User $actor): array;

    /** @return array<string, mixed> */
    public function report(ReportFilterData $filters, User $actor): array;
}
