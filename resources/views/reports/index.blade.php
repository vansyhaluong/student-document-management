@extends('layouts.app')

@section('title', 'Báo cáo')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Báo cáo']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <div>
            <p class="page-eyebrow">Báo cáo nghiệp vụ</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Báo cáo hồ sơ sinh viên</h1>
            <p class="mt-2 text-sm text-slate-500">Tổng hợp các hồ sơ trong phạm vi quyền truy cập của bạn.</p>
        </div>

        <section class="mt-6 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('reports.index') }}" class="grid gap-3 lg:grid-cols-4">
                <div>
                    <label for="document_type_id" class="mb-2 block text-xs font-semibold text-slate-600">Loại hồ sơ</label>
                    <select id="document_type_id" name="document_type_id" class="filter-control">
                        <option value="">Tất cả loại</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) ($filters['document_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="mb-2 block text-xs font-semibold text-slate-600">Trạng thái</label>
                    <select id="status" name="status" class="filter-control">
                        <option value="">Tất cả trạng thái</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="submitted_from" class="mb-2 block text-xs font-semibold text-slate-600">Nộp từ ngày</label>
                    <input id="submitted_from" name="submitted_from" type="date" value="{{ $filters['submitted_from'] ?? '' }}" class="filter-control">
                </div>
                <div>
                    <label for="submitted_to" class="mb-2 block text-xs font-semibold text-slate-600">Nộp đến ngày</label>
                    <input id="submitted_to" name="submitted_to" type="date" value="{{ $filters['submitted_to'] ?? '' }}" class="filter-control">
                </div>
                <div>
                    <label for="completed_from" class="mb-2 block text-xs font-semibold text-slate-600">Hoàn tất từ ngày</label>
                    <input id="completed_from" name="completed_from" type="date" value="{{ $filters['completed_from'] ?? '' }}" class="filter-control">
                </div>
                <div>
                    <label for="completed_to" class="mb-2 block text-xs font-semibold text-slate-600">Hoàn tất đến ngày</label>
                    <input id="completed_to" name="completed_to" type="date" value="{{ $filters['completed_to'] ?? '' }}" class="filter-control">
                </div>
                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit" class="btn-primary flex-1">Lọc báo cáo</button>
                    <a href="{{ route('reports.index') }}" class="btn-secondary px-3">Xóa lọc</a>
                </div>
            </form>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="surface-card border-l-4 border-l-faculty-600 sm:col-span-2 xl:col-span-1">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Tổng hồ sơ phù hợp</p>
                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ number_format($report['total']) }}</p>
            </article>
            <article class="surface-card sm:col-span-2 xl:col-span-3">
                <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Bộ lọc đang áp dụng</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                    @forelse ($filters as $field => $value)
                        @if ($value !== null && $value !== '')
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-faculty-800">{{ match($field) { 'document_type_id' => 'Loại hồ sơ', 'status' => 'Trạng thái', 'submitted_from' => 'Nộp từ', 'submitted_to' => 'Nộp đến', 'completed_from' => 'Hoàn tất từ', 'completed_to' => 'Hoàn tất đến' } }}: {{ $value }}</span>
                        @endif
                    @empty
                        <span>Không có bộ lọc; đang tổng hợp toàn bộ hồ sơ trong phạm vi truy cập.</span>
                    @endforelse
                </div>
            </article>
        </section>

        @if ($report['total'] === 0)
            <section class="mt-6 rounded-xl border border-blue-100 bg-white shadow-sm">
                <x-empty-state title="Không có dữ liệu phù hợp" description="Thử điều chỉnh hoặc xóa bộ lọc để xem số liệu tổng hợp." />
            </section>
        @else
            <section class="mt-6 grid gap-6 xl:grid-cols-2">
                <article class="surface-card">
                    <h2 class="text-lg font-semibold text-slate-950">Theo trạng thái</h2>
                    <div class="mt-4 divide-y divide-slate-100">
                        @foreach ($report['byStatus'] as $item)
                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0"><x-status-badge :status="$item->status" /><span class="text-sm font-semibold text-slate-900">{{ $item->total }}</span></div>
                        @endforeach
                    </div>
                </article>
                <article class="surface-card">
                    <h2 class="text-lg font-semibold text-slate-950">Theo loại hồ sơ</h2>
                    <div class="mt-4 divide-y divide-slate-100">
                        @foreach ($report['byType'] as $item)
                            <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0"><span class="text-sm text-slate-700">{{ $item->document_type_name }}</span><span class="text-sm font-semibold text-slate-900">{{ $item->total }}</span></div>
                        @endforeach
                    </div>
                </article>
                <article class="surface-card">
                    <h2 class="text-lg font-semibold text-slate-950">Theo ngày nộp và hoàn tất</h2>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div><p class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">Ngày nộp</p>@forelse ($report['submittedByDate'] as $item)<div class="flex justify-between gap-3 py-1.5 text-sm"><span class="text-slate-600">{{ \Illuminate\Support\Carbon::parse($item->report_date)->format('d/m/Y') }}</span><span class="font-semibold">{{ $item->total }}</span></div>@empty<p class="text-sm text-slate-500">Chưa có dữ liệu.</p>@endforelse</div>
                        <div><p class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">Ngày hoàn tất</p>@forelse ($report['completedByDate'] as $item)<div class="flex justify-between gap-3 py-1.5 text-sm"><span class="text-slate-600">{{ \Illuminate\Support\Carbon::parse($item->report_date)->format('d/m/Y') }}</span><span class="font-semibold">{{ $item->total }}</span></div>@empty<p class="text-sm text-slate-500">Chưa có dữ liệu.</p>@endforelse</div>
                    </div>
                </article>
            </section>
        @endif
    </div>
@endsection
