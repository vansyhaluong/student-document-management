@extends('layouts.app')

@section('title', $document->document_code)
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Hồ sơ sinh viên', 'url' => route('documents.index')], ['label' => $document->document_code]]" />
@endsection

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="page-eyebrow">Chi tiết hồ sơ</p>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold tracking-tight text-slate-950">{{ $document->document_code }}</h1>
                    <x-status-badge :status="$document->status" />
                </div>
                <p class="mt-2 text-sm text-slate-500">Cập nhật lần cuối {{ $document->updated_at->format('d/m/Y H:i') }}</p>
            </div>
            @can('update', $document)
                <a href="{{ route('documents.edit', $document) }}" class="btn-secondary">Cập nhật thông tin</a>
            @endcan
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <section class="surface-card">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div><p class="page-eyebrow">Thông tin chính</p><h2 class="mt-1 text-lg font-semibold text-slate-950">Sinh viên và hồ sơ</h2></div>
                        <span class="rounded-lg bg-blue-50 px-3 py-2 font-mono text-xs font-bold text-faculty-900">{{ $document->student_code }}</span>
                    </div>
                    <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Sinh viên</dt><dd class="mt-1.5 font-semibold text-slate-900">{{ $document->student?->full_name ?? $document->student_code }}</dd></div>
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Loại hồ sơ</dt><dd class="mt-1.5 text-slate-800">{{ $document->documentType?->name ?? 'Không xác định' }}</dd></div>
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Người phụ trách</dt><dd class="mt-1.5 text-slate-800">{{ $document->responsibleUser?->full_name ?? 'Chưa phân công' }}</dd></div>
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Ngày nộp</dt><dd class="mt-1.5 text-slate-800">{{ $document->submitted_at->format('d/m/Y H:i') }}</dd></div>
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Ngày hoàn tất</dt><dd class="mt-1.5 text-slate-800">{{ $document->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                        <div><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Lý do không hợp lệ</dt><dd class="mt-1.5 text-slate-800">{{ $document->invalid_reason ?? '—' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Ghi chú hồ sơ</dt><dd class="mt-1.5 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $document->note ?? 'Không có ghi chú.' }}</dd></div>
                    </dl>
                </section>

                <section class="surface-card">
                    <p class="page-eyebrow">Dòng thời gian</p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-950">Lịch sử trạng thái</h2>
                    <ol class="mt-6 space-y-0">
                        @forelse ($document->statusHistory as $entry)
                            <li class="relative grid grid-cols-[1.25rem_1fr] gap-3 pb-6 last:pb-0">
                                <span class="absolute top-4 bottom-0 left-[0.34rem] w-px bg-blue-100 last:hidden"></span>
                                <span class="relative mt-1 size-3 rounded-full bg-faculty-600 ring-4 ring-blue-50"></span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2"><x-status-badge :status="$entry->status" /><time class="text-xs text-slate-500">{{ $entry->changed_at->format('d/m/Y H:i') }}</time></div>
                                    <p class="mt-2 text-sm font-medium text-slate-800">{{ $entry->changedBy?->full_name ?? 'Không xác định' }}</p>
                                    @if ($entry->note)<p class="mt-1 text-sm text-slate-600">{{ $entry->note }}</p>@endif
                                    @if ($entry->invalid_reason)<p class="mt-1 text-sm font-medium text-red-700">{{ $entry->invalid_reason }}</p>@endif
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-slate-500">Chưa có lịch sử trạng thái.</p>
                        @endforelse
                    </ol>
                </section>
            </div>

            <aside class="space-y-5">
                @if ($document->status === \App\Enums\StudentDocumentStatus::WAITING_FOR_RECEIPT && auth()->user()->can('accept', $document))
                    <section class="rounded-xl border border-sky-200 bg-sky-50 p-5">
                        <p class="text-xs font-semibold tracking-[0.14em] text-sky-800 uppercase">Bước tiếp theo</p><h2 class="mt-2 font-semibold text-slate-950">Tiếp nhận hồ sơ</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Xác nhận hồ sơ đã đến người phụ trách và bắt đầu quy trình xử lý.</p>
                        <form method="POST" action="{{ route('documents.accept', $document) }}" class="mt-4">
                            @csrf
                            <textarea name="transition_note" rows="3" maxlength="500" class="form-control" placeholder="Ghi chú tiếp nhận (nếu có)"></textarea>
                            <button type="submit" class="btn-primary mt-3 w-full">Xác nhận tiếp nhận</button>
                        </form>
                    </section>
                @endif

                @can('changeStatus', $document)
                    @if ($availableTransitions->isNotEmpty())
                        <section class="surface-card">
                            <p class="page-eyebrow">Workflow</p><h2 class="mt-1 font-semibold text-slate-950">Chuyển trạng thái</h2>
                            <form method="POST" action="{{ route('documents.status', $document) }}" class="mt-4 space-y-3" data-status-form>
                                @csrf @method('PATCH')
                                <select name="status" required class="form-control" data-status-select>
                                    <option value="">Chọn trạng thái tiếp theo</option>
                                    @foreach ($availableTransitions as $status)
                                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <div data-invalid-reason @class(['hidden' => old('status') !== \App\Enums\StudentDocumentStatus::INVALID->value])>
                                    <label for="invalid_reason" class="mb-2 block text-sm font-semibold text-slate-700">Lý do không hợp lệ</label>
                                    <textarea id="invalid_reason" name="invalid_reason" rows="3" maxlength="200" class="form-control">{{ old('invalid_reason') }}</textarea>
                                    @error('invalid_reason')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <textarea name="transition_note" rows="3" maxlength="500" class="form-control" placeholder="Ghi chú chuyển trạng thái (nếu có)">{{ old('transition_note') }}</textarea>
                                <button type="submit" class="btn-primary w-full">Cập nhật trạng thái</button>
                            </form>
                        </section>
                    @endif
                @endcan
            </aside>
        </div>
    </div>
@endsection
