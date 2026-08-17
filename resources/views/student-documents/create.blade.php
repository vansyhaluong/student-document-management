@extends('layouts.app')

@section('title', 'Tạo hồ sơ')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Hồ sơ sinh viên', 'url' => route('documents.index')], ['label' => 'Tạo hồ sơ']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <p class="page-eyebrow">Hồ sơ mới</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Tạo hồ sơ sinh viên</h1>
        <p class="mt-2 text-sm text-slate-500">Hồ sơ mới bắt đầu ở trạng thái Chờ tiếp nhận và tự ghi nhận thời gian nộp.</p>

        <form method="POST" action="{{ route('documents.store') }}" class="surface-card mt-6">
            @csrf
            @include('student-documents.partials.form')
            <div class="mt-7 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('documents.index') }}" class="btn-secondary">Hủy</a>
                <button type="submit" class="btn-primary">Tạo hồ sơ</button>
            </div>
        </form>
    </div>
@endsection
