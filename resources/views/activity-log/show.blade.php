@extends('layouts.app')

@section('title', 'Chi tiết nhật ký')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Nhật ký hoạt động', 'url' => route('activity-log.index')], ['label' => 'Chi tiết']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="page-eyebrow">Bản ghi chỉ đọc</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $description }}</h1>
                <p class="mt-2 font-mono text-sm text-slate-500">{{ $activityLog->event ?? 'Không xác định' }}</p>
            </div>
            <a href="{{ route('activity-log.index') }}" class="btn-secondary w-fit">Quay lại nhật ký</a>
        </div>

        <section class="mt-6 overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm">
            <dl class="divide-y divide-slate-100">
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-5"><dt class="text-sm font-semibold text-slate-600">Người thực hiện</dt><dd class="text-sm text-slate-900 sm:col-span-2">{{ $activityLog->actor_name ?? 'Hệ thống hoặc không xác định' }}</dd></div>
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-5"><dt class="text-sm font-semibold text-slate-600">Đối tượng</dt><dd class="text-sm text-slate-900 sm:col-span-2">{{ $subjectLabel }} #{{ $activityLog->subject_id ?? '—' }}</dd></div>
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-5"><dt class="text-sm font-semibold text-slate-600">Thời điểm</dt><dd class="text-sm text-slate-900 sm:col-span-2">{{ $activityLog->created_at?->format('d/m/Y H:i:s') ?? '—' }}</dd></div>
                <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-5"><dt class="text-sm font-semibold text-slate-600">Ngữ cảnh an toàn</dt><dd class="sm:col-span-2">@if ($metadata === [])<p class="text-sm text-slate-500">Không có metadata an toàn để hiển thị.</p>@else<dl class="space-y-2">@foreach ($metadata as $key => $value)<div class="flex flex-col gap-1 text-sm sm:flex-row sm:gap-4"><dt class="min-w-44 font-medium text-slate-600">{{ str_replace('_', ' ', $key) }}</dt><dd class="font-mono text-xs leading-6 text-slate-900">{{ is_array($value) ? implode(', ', $value) : $value }}</dd></div>@endforeach</dl>@endif</dd></div>
            </dl>
        </section>
    </div>
@endsection
