@extends('layouts.app')

@section('title', 'Loại hồ sơ')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Loại hồ sơ']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="page-eyebrow">Danh mục nghiệp vụ</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Loại hồ sơ</h1>
                <p class="mt-2 text-sm text-slate-500">Loại đã tắt vẫn được giữ trên hồ sơ cũ nhưng không dùng cho hồ sơ mới.</p>
            </div>
            <a href="{{ route('document-types.create') }}" class="btn-primary">
                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" /></svg>
                Thêm loại hồ sơ
            </a>
        </div>

        <section class="mt-6 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('document-types.index') }}" class="grid gap-3 md:grid-cols-[minmax(16rem,1fr)_13rem_auto] md:items-end">
                <div>
                    <label for="keyword" class="mb-2 block text-xs font-semibold text-slate-600">Tìm kiếm</label>
                    <input id="keyword" name="keyword" type="search" value="{{ $filters['keyword'] ?? '' }}" placeholder="Mã, tên hoặc mô tả" class="filter-control">
                </div>
                <div>
                    <label for="is_active" class="mb-2 block text-xs font-semibold text-slate-600">Trạng thái</label>
                    <select id="is_active" name="is_active" class="filter-control">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Đang sử dụng</option>
                        <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Đã tắt</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Lọc</button>
                    <a href="{{ route('document-types.index') }}" class="btn-secondary px-3">Xóa lọc</a>
                </div>
            </form>
        </section>

        <div class="mt-5">
            <x-table :empty="$documentTypes->isEmpty()">
                <x-slot:head>
                    <tr>
                        <th class="table-heading">Mã loại</th>
                        <th class="table-heading">Tên loại hồ sơ</th>
                        <th class="table-heading">Mô tả</th>
                        <th class="table-heading">Trạng thái</th>
                        <th class="table-heading text-right">Thao tác</th>
                    </tr>
                </x-slot:head>

                <x-slot:emptyState>
                    <x-empty-state title="Không tìm thấy loại hồ sơ" description="Thử thay đổi từ khóa hoặc xóa bộ lọc để xem lại danh sách.">
                        <a href="{{ route('document-types.create') }}" class="text-sm font-semibold text-faculty-800 hover:text-faculty-900">Thêm loại hồ sơ</a>
                    </x-empty-state>
                </x-slot:emptyState>

                @foreach ($documentTypes as $documentType)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="table-cell"><span class="rounded-md bg-blue-50 px-2.5 py-1.5 font-mono text-xs font-semibold text-faculty-900 ring-1 ring-inset ring-blue-100">{{ $documentType->code }}</span></td>
                        <td class="table-cell font-semibold text-slate-900">{{ $documentType->name }}</td>
                        <td class="table-cell max-w-md text-sm text-slate-500"><p class="line-clamp-2">{{ $documentType->description ?: '—' }}</p></td>
                        <td class="table-cell">
                            <span @class([
                                'status-pill',
                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $documentType->is_active,
                                'bg-slate-100 text-slate-600 ring-slate-500/20' => ! $documentType->is_active,
                            ])>
                                <span @class(['size-1.5 rounded-full', 'bg-emerald-500' => $documentType->is_active, 'bg-slate-400' => ! $documentType->is_active])></span>
                                {{ $documentType->is_active ? 'Đang sử dụng' : 'Đã tắt' }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex flex-wrap justify-end gap-1.5">
                                <a href="{{ route('document-types.edit', $documentType) }}" class="table-action">Chỉnh sửa</a>
                                <form method="POST" action="{{ route('document-types.status', $documentType) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class(['table-action', 'text-red-700 hover:bg-red-50' => $documentType->is_active, 'text-emerald-700 hover:bg-emerald-50' => ! $documentType->is_active])>
                                        {{ $documentType->is_active ? 'Tắt' : 'Bật' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$documentTypes" />
        </div>
    </div>
@endsection
