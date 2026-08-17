<?php

namespace App\Services;

use App\Enums\StudentDocumentStatus;
use App\Models\User;
use App\Repositories\Contracts\ReportRepository;

class DashboardService
{
    public function __construct(
        private readonly ReportRepository $reports,
    ) {}

    /** @return array<string, mixed> */
    public function summary(User $actor): array
    {
        $summary = $this->reports->dashboard($actor);
        $statusCounts = collect($summary['byStatus'])->mapWithKeys(
            static fn ($item): array => [$item->getRawOriginal('status') => $item->total],
        );

        $summary['statusOverview'] = collect(StudentDocumentStatus::cases())
            ->map(static fn (StudentDocumentStatus $status): array => [
                'status' => $status,
                'total' => (int) ($statusCounts->get($status->value) ?? 0),
            ]);

        return $summary;
    }
}
