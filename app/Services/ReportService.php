<?php

namespace App\Services;

use App\DTOs\ReportFilterData;
use App\Enums\StudentDocumentStatus;
use App\Models\User;
use App\Repositories\Contracts\DocumentTypeRepository;
use App\Repositories\Contracts\ReportRepository;

class ReportService
{
    public function __construct(
        private readonly ReportRepository $reports,
        private readonly DocumentTypeRepository $documentTypes,
    ) {}

    /** @return array<string, mixed> */
    public function indexData(ReportFilterData $filters, User $actor): array
    {
        return [
            'report' => $this->reports->report($filters, $actor),
            'documentTypes' => $this->documentTypes->allOrdered(),
            'statuses' => StudentDocumentStatus::cases(),
        ];
    }
}
