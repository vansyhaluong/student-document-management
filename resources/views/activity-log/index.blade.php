@extends('layouts.app')

@section('title', 'Nhật ký hoạt động')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Nhật ký hoạt động']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <div>
            <p class="page-eyebrow">Kiểm soát hoạt động</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Nhật ký hoạt động</h1>
            <p class="mt-2 text-sm text-slate-500">Dữ liệu chỉ đọc, dùng để truy vết những thao tác nghiệp vụ và bảo mật quan trọng.</p>
        </div>

        <section class="mt-6 rounded-xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('activity-log.index') }}" class="grid gap-3 lg:grid-cols-4">
                <div>
                    <label for="event" class="mb-2 block text-xs font-semibold text-slate-600">Mã sự kiện</label>
                    <input id="event" name="event" value="{{ $filters['event'] ?? '' }}" placeholder="Ví dụ: user.created" class="filter-control">
                </div>
                <div>
                    <label for="actor_user_id" class="mb-2 block text-xs font-semibold text-slate-600">Người thực hiện</label>
                    <select id="actor_user_id" name="actor_user_id" class="filter-control">
                        <option value="">Tất cả người dùng</option>
                        @foreach ($actorUsers as $user)
                            <option value="{{ $user->id }}" @selected((string) ($filters['actor_user_id'] ?? '') === (string) $user->id)>{{ $user->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject_type" class="mb-2 block text-xs font-semibold text-slate-600">Đối tượng</label>
                    <select id="subject_type" name="subject_type" class="filter-control">
                        <option value="">Tất cả đối tượng</option>
                        @foreach ($subjectTypes as $type => $label)
                            <option value="{{ $type }}" @selected(($filters['subject_type'] ?? '') === $type)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject_id" class="mb-2 block text-xs font-semibold text-slate-600">ID đối tượng</label>
                    <input id="subject_id" name="subject_id" type="number" min="1" value="{{ $filters['subject_id'] ?? '' }}" class="filter-control">
                </div>
                <div>
                    <label for="from" class="mb-2 block text-xs font-semibold text-slate-600">Từ ngày</label>
                    <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="filter-control">
                </div>
                <div>
                    <label for="to" class="mb-2 block text-xs font-semibold text-slate-600">Đến ngày</label>
                    <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="filter-control">
                </div>
                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit" class="btn-primary flex-1">Lọc nhật ký</button>
                    <a href="{{ route('activity-log.index') }}" class="btn-secondary px-3">Xóa lọc</a>
                </div>
            </form>
        </section>

        <div class="mt-5">
            <x-table :empty="$activityLogs->isEmpty()">
                <x-slot:head>
                    <tr>
                        <th class="table-heading">Sự kiện</th>
                        <th class="table-heading">Người thực hiện</th>
                        <th class="table-heading">Đối tượng</th>
                        <th class="table-heading">Thời điểm</th>
                        <th class="table-heading text-right">Chi tiết</th>
                    </tr>
                </x-slot:head>
                <x-slot:emptyState>
                    <x-empty-state title="Không tìm thấy nhật ký" description="Thử điều chỉnh điều kiện lọc để xem các hoạt động đã được ghi nhận." />
                </x-slot:emptyState>

                @foreach ($activityLogs as $activityLog)
                    <tr class="transition hover:bg-slate-50/80">
                        <td class="table-cell">
                            <p class="font-semibold text-slate-900">{{ $activityLog->display_description }}</p>
                            <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $activityLog->event ?? 'Không xác định' }}</p>
                        </td>
                        <td class="table-cell text-sm text-slate-600">{{ $activityLog->actor_name ?? 'Hệ thống hoặc không xác định' }}</td>
                        <td class="table-cell text-sm text-slate-600">{{ $activityLog->display_subject_label }} #{{ $activityLog->subject_id ?? '—' }}</td>
                        <td class="table-cell whitespace-nowrap text-sm text-slate-600">{{ $activityLog->created_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                        <td class="table-cell text-right"><a href="{{ route('activity-log.show', $activityLog->id) }}" class="table-action">Xem chi tiết</a></td>
                    </tr>
                @endforeach
            </x-table>
            <x-pagination :paginator="$activityLogs" />
        </div>
    </div>
@endsection
