@extends('layouts.app')

@section('title', 'Tổng quan')

@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Tổng quan']]" />
@endsection

@section('content')
@php
    $statusThemes = [
        \App\Enums\StudentDocumentStatus::WAITING_FOR_RECEIPT->value => [
            'card' => 'border-l-amber-500',
            'bar' => 'bg-amber-500',
            'hex' => '#f59e0b',
        ],
        \App\Enums\StudentDocumentStatus::RECEIVED->value => [
            'card' => 'border-l-sky-500',
            'bar' => 'bg-sky-500',
            'hex' => '#0ea5e9',
        ],
        \App\Enums\StudentDocumentStatus::PROCESSING->value => [
            'card' => 'border-l-blue-500',
            'bar' => 'bg-blue-500',
            'hex' => '#3b82f6',
        ],
        \App\Enums\StudentDocumentStatus::NEEDS_SUPPLEMENT->value => [
            'card' => 'border-l-orange-500',
            'bar' => 'bg-orange-500',
            'hex' => '#f97316',
        ],
        \App\Enums\StudentDocumentStatus::COMPLETED->value => [
            'card' => 'border-l-emerald-500',
            'bar' => 'bg-emerald-500',
            'hex' => '#10b981',
        ],
        \App\Enums\StudentDocumentStatus::INVALID->value => [
            'card' => 'border-l-red-500',
            'bar' => 'bg-red-500',
            'hex' => '#ef4444',
        ],
        \App\Enums\StudentDocumentStatus::CANCELLED->value => [
            'card' => 'border-l-slate-400',
            'bar' => 'bg-slate-400',
            'hex' => '#94a3b8',
        ],
    ];
    $kpiStatuses = $summary['statusOverview']->take(3);
    $donutRadius = 56;
    $donutCircumference = 2 * M_PI * $donutRadius;
    $donutOffset = 0;
    $donutSegments = [];

    foreach ($summary['statusOverview'] as $item) {
        if ($item['total'] <= 0 || $summary['total'] <= 0) {
            continue;
        }

        $length = ($item['total'] / $summary['total']) * $donutCircumference;
        $donutSegments[] = [
            'label' => $item['status']->label(),
            'hex' => $statusThemes[$item['status']->value]['hex'],
            'length' => $length,
            'offset' => $donutOffset,
        ];
        $donutOffset += $length;
    }

    $typeMax = max(1, (int) $summary['byType']->max('total'));
@endphp
<div class="mx-auto max-w-7xl">
    <section class="dashboard-hero">
        <p class="page-eyebrow">Không gian làm việc</p>
        <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink-950 sm:text-3xl">Xin chào, {{ auth()->user()->full_name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ auth()->user()->role->label() }} · Dữ liệu dưới đây chỉ bao gồm những hồ sơ bạn được phép truy cập.
                </p>
            </div>
            <a href="{{ route('documents.index') }}" class="btn-secondary w-fit bg-white">Xem danh sách hồ sơ</a>
        </div>
    </section>

    @if ($summary['total'] === 0)
    <section class="mt-6 rounded-xl border border-faculty-100 bg-white shadow-sm">
        <x-empty-state title="Chưa có hồ sơ trong phạm vi truy cập" description="Số liệu tổng quan sẽ xuất hiện khi có hồ sơ phù hợp với quyền truy cập của bạn." />
    </section>
    @else
    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="dashboard-kpi border-l-faculty-600">
            <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Tổng hồ sơ</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight text-ink-950">{{ number_format($summary['total']) }}</p>
            <p class="mt-2 text-sm text-slate-500">Trong phạm vi được xem</p>
        </article>
        @foreach ($kpiStatuses as $item)
        <article class="dashboard-kpi {{ $statusThemes[$item['status']->value]['card'] }}">
            <x-status-badge :status="$item['status']" />
            <p class="mt-3 text-3xl font-semibold tracking-tight text-ink-950">{{ number_format($item['total']) }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $item['status']->label() }}</p>
        </article>
        @endforeach
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <article class="surface-card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="page-eyebrow">Theo trạng thái</p>
                    <h2 class="mt-1 text-lg font-semibold text-ink-950">Tỉ lệ xử lý hồ sơ</h2>
                </div>
                <span class="text-sm text-slate-500">{{ number_format($summary['total']) }} hồ sơ</span>
            </div>

            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row sm:items-center">
                <figure class="dashboard-donut" aria-label="Biểu đồ tỉ lệ hồ sơ theo trạng thái">
                    <svg viewBox="0 0 160 160" role="img">
                        <title>Phân bố hồ sơ theo trạng thái</title>
                        <circle cx="80" cy="80" r="56" fill="none" stroke="#e2e8f0" stroke-width="16"></circle>
                        @foreach ($donutSegments as $segment)
                        <circle
                            cx="80"
                            cy="80"
                            r="56"
                            fill="none"
                            stroke="{{ $segment['hex'] }}"
                            stroke-width="16"
                            stroke-linecap="butt"
                            stroke-dasharray="{{ $segment['length'] }} {{ $donutCircumference }}"
                            stroke-dashoffset="{{ -$segment['offset'] }}"
                            transform="rotate(-90 80 80)"
                        ></circle>
                        @endforeach
                        <text x="80" y="76" text-anchor="middle" class="dashboard-donut-total">{{ number_format($summary['total']) }}</text>
                        <text x="80" y="96" text-anchor="middle" class="dashboard-donut-caption">hồ sơ</text>
                    </svg>
                </figure>

                <ul class="min-w-0 flex-1 space-y-2.5">
                    @foreach ($summary['statusOverview'] as $item)
                    @php($percent = $summary['total'] > 0 ? round(($item['total'] / $summary['total']) * 100) : 0)
                    <li class="flex items-center justify-between gap-3 text-sm">
                        <span class="flex min-w-0 items-center gap-2">
                            <span class="size-2.5 shrink-0 rounded-full" style="background: {{ $statusThemes[$item['status']->value]['hex'] }}"></span>
                            <span class="truncate text-slate-700">{{ $item['status']->label() }}</span>
                        </span>
                        <span class="shrink-0 font-semibold text-slate-900">{{ $item['total'] }} <span class="font-normal text-slate-400">({{ $percent }}%)</span></span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </article>

        <article class="surface-card">
            <p class="page-eyebrow">Theo loại hồ sơ</p>
            <h2 class="mt-1 text-lg font-semibold text-ink-950">Phân bố loại hồ sơ</h2>

            @if ($summary['byType']->isEmpty())
            <p class="mt-6 text-sm text-slate-500">Chưa có dữ liệu loại hồ sơ.</p>
            @else
            <div class="dashboard-columns" role="img" aria-label="Biểu đồ cột theo loại hồ sơ">
                @foreach ($summary['byType'] as $item)
                @php($typePercent = (int) round(($item->total / $typeMax) * 100))
                <div class="dashboard-column">
                    <p class="dashboard-column-value">{{ $item->total }}</p>
                    <div class="dashboard-column-track">
                        <div class="dashboard-column-fill" style="height: {{ $typePercent }}%"></div>
                    </div>
                    <p class="dashboard-column-label" title="{{ $item->document_type_name }}">{{ $item->document_type_name }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <article class="surface-card">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="page-eyebrow">Cập nhật gần đây</p>
                    <h2 class="mt-1 text-lg font-semibold text-ink-950">Hồ sơ cần theo dõi</h2>
                </div>
                <a href="{{ route('documents.index') }}" class="table-action">Xem tất cả</a>
            </div>
            <div class="mt-4 space-y-1">
                @foreach ($summary['recentDocuments'] as $document)
                <a href="{{ route('documents.show', $document) }}" class="dashboard-recent-item">
                    <div class="min-w-0">
                        <p class="font-mono text-xs font-bold text-faculty-900">{{ $document->document_code }}</p>
                        <p class="mt-1 truncate text-sm font-medium text-slate-800">{{ $document->student?->full_name ?? $document->student_code }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <x-status-badge :status="$document->status" />
                        <p class="mt-1.5 text-xs text-slate-500">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </article>

        <article class="surface-card">
            <p class="page-eyebrow">Người phụ trách</p>
            <h2 class="mt-1 text-lg font-semibold text-ink-950">Khối lượng đang được giao</h2>
            <div class="mt-5 space-y-3">
                @foreach ($summary['byResponsibleUser'] as $item)
                @php($loadPercent = $summary['total'] > 0 ? (int) round(($item->total / $summary['total']) * 100) : 0)
                <div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="min-w-0 truncate text-slate-700">{{ $item->responsible_user_name ?? 'Chưa phân công' }}</span>
                        <span class="shrink-0 text-xs font-semibold text-faculty-800">{{ $item->total }} hồ sơ</span>
                    </div>
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-faculty-50">
                        <div class="h-full rounded-full bg-faculty-600" style="width: {{ $loadPercent }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </article>
    </section>
    @endif
</div>
@endsection
