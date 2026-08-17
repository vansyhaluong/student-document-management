@extends('layouts.app')

@section('title', 'Hồ sơ sinh viên')

@section('breadcrumb')
<x-breadcrumb :items="[['label' => 'Hồ sơ sinh viên']]" />
@endsection

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="page-eyebrow">Theo dõi nghiệp vụ</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Hồ sơ sinh viên</h1>
            <p class="mt-2 text-sm text-slate-500">Tra cứu hồ sơ và cập nhật tiến độ xử lý trong một danh sách.</p>
        </div>
        @can('create', \App\Models\StudentDocument::class)
        <a href="{{ route('documents.create') }}" class="btn-primary">
            <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Tạo hồ sơ
        </a>
        @endcan
    </div>

    <section class="mt-6 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('documents.index') }}" class="grid gap-3 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label for="keyword" class="mb-2 block text-xs font-semibold text-slate-600">Tìm kiếm</label>
                <input id="keyword" name="keyword" type="search" value="{{ $filters['keyword'] ?? '' }}" placeholder="Mã hồ sơ, mã hoặc tên sinh viên" class="filter-control">
            </div>
            <div>
                <label for="document_type_id" class="mb-2 block text-xs font-semibold text-slate-600">Loại hồ sơ</label>
                <select id="document_type_id" name="document_type_id" class="filter-control">
                    <option value="">Tất cả loại</option>
                    @foreach ($documentTypes as $type)
                    <option value="{{ $type->id }}" @selected((string) ($filters['document_type_id'] ?? '' )===(string) $type->id)>{{ $type->name }}{{ $type->is_active ? '' : ' (đã tắt)' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="mb-2 block text-xs font-semibold text-slate-600">Trạng thái</label>
                <select id="status" name="status" class="filter-control">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '' )===$status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            @unless(auth()->user()->hasRole(\App\Enums\UserRole::EMPLOYEE))
            <div>
                <label for="responsible_user_id" class="mb-2 block text-xs font-semibold text-slate-600">Người phụ trách</label>
                <select id="responsible_user_id" name="responsible_user_id" class="filter-control">
                    <option value="">Tất cả người phụ trách</option>
                    @foreach ($responsibleUsers as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filters['responsible_user_id'] ?? '' )===(string) $user->id)>{{ $user->full_name }}{{ $user->is_active ? '' : ' (đã khóa)' }}</option>
                    @endforeach
                </select>
            </div>
            @endunless
            <div>
                <label for="submitted_from" class="mb-2 block text-xs font-semibold text-slate-600">Nộp từ ngày</label>
                <input id="submitted_from" name="submitted_from" type="date" value="{{ $filters['submitted_from'] ?? '' }}" class="filter-control">
            </div>
            <div>
                <label for="submitted_to" class="mb-2 block text-xs font-semibold text-slate-600">Đến ngày</label>
                <input id="submitted_to" name="submitted_to" type="date" value="{{ $filters['submitted_to'] ?? '' }}" class="filter-control">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Lọc hồ sơ</button>
                <a href="{{ route('documents.index') }}" class="btn-secondary px-3">Xóa lọc</a>
            </div>
        </form>
    </section>

    <div class="mt-5">
        <x-table :empty="$documents->isEmpty()">
            <x-slot:head>
                <tr>
                    <th class="table-heading">Mã hồ sơ</th>
                    <th class="table-heading">Sinh viên</th>
                    <th class="table-heading">Loại hồ sơ</th>
                    <th class="table-heading">Trạng thái</th>
                    <th class="table-heading">Ngày nộp</th>
                    <th class="table-heading text-right">Thao tác</th>
                </tr>
            </x-slot:head>
            <x-slot:emptyState>
                <x-empty-state title="Không tìm thấy hồ sơ" description="Thử thay đổi từ khóa hoặc xóa bộ lọc để xem lại danh sách." />
            </x-slot:emptyState>

            @foreach ($documents as $document)
            <tr class="transition hover:bg-slate-50/80">
                <td class="table-cell"><a href="{{ route('documents.show', $document) }}" class="font-mono text-xs font-bold text-faculty-900 hover:underline">{{ $document->document_code }}</a></td>
                <td class="table-cell">
                    <p class="font-semibold text-slate-900">{{ $document->student?->full_name ?? $document->student_code }}</p>
                    <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $document->student_code }}</p>
                </td>
                <td class="table-cell text-sm text-slate-600">{{ $document->documentType?->name ?? 'Không xác định' }}</td>
                <td class="table-cell min-w-52">
                    @php
                        $availableTransitions = [];
                        foreach (\App\Enums\StudentDocumentStatus::cases() as $nextStatus) {
                            if ($document->status->canTransitionTo($nextStatus)) {
                                $availableTransitions[] = $nextStatus;
                            }
                        }
                    @endphp
                    @can('changeStatus', $document)
                        @if ($availableTransitions !== [])
                            <form method="POST" action="{{ route('documents.status', $document) }}" class="space-y-2" data-status-form data-status-autosubmit>
                                @csrf
                                @method('PATCH')
                                <select name="status" required class="filter-control" data-status-select aria-label="Trạng thái hồ sơ {{ $document->document_code }}">
                                    <option value="">{{ $document->status->label() }}</option>
                                    @foreach ($availableTransitions as $status)
                                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <div data-invalid-reason class="hidden space-y-2">
                                    <textarea name="invalid_reason" rows="2" maxlength="200" class="form-control" placeholder="Lý do không hợp lệ"></textarea>
                                    <button type="submit" class="btn-primary w-full">Cập nhật</button>
                                </div>
                            </form>
                        @else
                            <x-status-badge :status="$document->status" />
                        @endif
                    @else
                        <div class="flex flex-col items-start gap-2">
                            <x-status-badge :status="$document->status" />
                            @if ($document->status === \App\Enums\StudentDocumentStatus::WAITING_FOR_RECEIPT)
                                @can('accept', $document)
                                    <form method="POST" action="{{ route('documents.accept', $document) }}">
                                        @csrf
                                        <button type="submit" class="btn-secondary px-3 text-xs">Tiếp nhận</button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    @endcan
                </td>
                <td class="table-cell whitespace-nowrap text-sm text-slate-600">{{ $document->submitted_at->format('d/m/Y H:i') }}</td>
                <td class="table-cell text-right"><a href="{{ route('documents.show', $document) }}" class="table-action">Xem chi tiết</a></td>
            </tr>
            @endforeach
        </x-table>
        <x-pagination :paginator="$documents" />
    </div>
</div>
@endsection