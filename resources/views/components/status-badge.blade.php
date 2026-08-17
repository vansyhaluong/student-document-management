@props(['status'])

@php
    $statusValue = $status instanceof \App\Enums\StudentDocumentStatus ? $status : \App\Enums\StudentDocumentStatus::tryFrom((string) $status);
    $classes = match ($statusValue) {
        \App\Enums\StudentDocumentStatus::WAITING_FOR_RECEIPT => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        \App\Enums\StudentDocumentStatus::RECEIVED => 'bg-sky-50 text-sky-800 ring-sky-600/20',
        \App\Enums\StudentDocumentStatus::PROCESSING => 'bg-blue-50 text-blue-800 ring-blue-600/20',
        \App\Enums\StudentDocumentStatus::NEEDS_SUPPLEMENT => 'bg-orange-50 text-orange-800 ring-orange-600/20',
        \App\Enums\StudentDocumentStatus::COMPLETED => 'bg-emerald-50 text-emerald-800 ring-emerald-600/20',
        \App\Enums\StudentDocumentStatus::INVALID => 'bg-red-50 text-red-800 ring-red-600/20',
        \App\Enums\StudentDocumentStatus::CANCELLED => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}") }}>
    {{ $statusValue?->label() ?? 'Không xác định' }}
</span>
