@extends('layouts.app')

@section('title', 'Cập nhật hồ sơ')
@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Hồ sơ sinh viên', 'url' => route('documents.index')], ['label' => $document->document_code, 'url' => route('documents.show', $document)], ['label' => 'Cập nhật']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <p class="page-eyebrow">Điều chỉnh thông tin</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Cập nhật hồ sơ</h1>
        <p class="mt-2 text-sm text-slate-500">Mã hồ sơ và trạng thái được quản lý riêng để bảo toàn lịch sử nghiệp vụ.</p>

        <form method="POST" action="{{ route('documents.update', $document) }}" class="surface-card mt-6">
            @csrf
            @method('PUT')
            @include('student-documents.partials.form')
            <div class="mt-7 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('documents.show', $document) }}" class="btn-secondary">Hủy</a>
                <button type="submit" class="btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
@endsection
