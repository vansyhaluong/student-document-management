@extends('layouts.app')

@section('title', 'Người dùng')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Người dùng']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="page-eyebrow">Quản trị truy cập</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Người dùng</h1>
                <p class="mt-2 text-sm text-slate-500">Quản lý tài khoản, vai trò và trạng thái truy cập hệ thống.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" /></svg>
                Tạo tài khoản
            </a>
        </div>

        <section class="mt-6 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('users.index') }}" class="grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_13rem_12rem_auto] lg:items-end">
                <div>
                    <label for="keyword" class="mb-2 block text-xs font-semibold text-slate-600">Tìm kiếm</label>
                    <input id="keyword" name="keyword" type="search" value="{{ $filters['keyword'] ?? '' }}" placeholder="Họ tên hoặc username" class="filter-control">
                </div>
                <div>
                    <label for="role" class="mb-2 block text-xs font-semibold text-slate-600">Vai trò</label>
                    <select id="role" name="role" class="filter-control">
                        <option value="">Tất cả vai trò</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role['value'] }}" @selected(($filters['role'] ?? '') === $role['value'])>{{ $role['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="is_active" class="mb-2 block text-xs font-semibold text-slate-600">Trạng thái</label>
                    <select id="is_active" name="is_active" class="filter-control">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Đang hoạt động</option>
                        <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Đã khóa</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Lọc</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary px-3">Xóa lọc</a>
                </div>
            </form>
        </section>

        <div class="mt-5">
            <x-table :empty="$users->isEmpty()">
                <x-slot:head>
                    <tr>
                        <th class="table-heading">Người dùng</th>
                        <th class="table-heading">Vai trò</th>
                        <th class="table-heading">Trạng thái</th>
                        <th class="table-heading">Ngày tạo</th>
                        <th class="table-heading text-right">Thao tác</th>
                    </tr>
                </x-slot:head>

                <x-slot:emptyState>
                    <x-empty-state title="Không tìm thấy người dùng" description="Thử thay đổi từ khóa hoặc xóa bộ lọc để xem lại danh sách.">
                        <a href="{{ route('users.create') }}" class="text-sm font-semibold text-faculty-800 hover:text-faculty-900">Tạo tài khoản mới</a>
                    </x-empty-state>
                </x-slot:emptyState>

                @foreach ($users as $managedUser)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-blue-50 text-sm font-bold text-faculty-800 ring-1 ring-blue-100">{{ mb_strtoupper(mb_substr($managedUser->full_name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $managedUser->full_name }}</p>
                                    <p class="mt-0.5 truncate font-mono text-xs text-slate-500">{{ $managedUser->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell"><span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-faculty-800 ring-1 ring-inset ring-blue-100">{{ $managedUser->role->label() }}</span></td>
                        <td class="table-cell">
                            <span @class([
                                'status-pill',
                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $managedUser->is_active,
                                'bg-slate-100 text-slate-600 ring-slate-500/20' => ! $managedUser->is_active,
                            ])>
                                <span @class(['size-1.5 rounded-full', 'bg-emerald-500' => $managedUser->is_active, 'bg-slate-400' => ! $managedUser->is_active])></span>
                                {{ $managedUser->is_active ? 'Đang hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td class="table-cell text-sm text-slate-500">{{ $managedUser->created_at?->format('d/m/Y') }}</td>
                        <td class="table-cell">
                            <div class="flex flex-wrap justify-end gap-1.5">
                                <a href="{{ route('users.edit', $managedUser) }}" class="table-action">Chỉnh sửa</a>
                                @if (auth()->id() !== $managedUser->id)
                                    <form method="POST" action="{{ route('users.status', $managedUser) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" @class(['table-action', 'text-red-700 hover:bg-red-50' => $managedUser->is_active, 'text-emerald-700 hover:bg-emerald-50' => ! $managedUser->is_active])>
                                            {{ $managedUser->is_active ? 'Khóa' : 'Mở khóa' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex min-h-9 items-center px-2 text-xs font-medium text-slate-400">Tài khoản hiện tại</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <x-pagination :paginator="$users" />
        </div>
    </div>
@endsection
