@extends('layouts.app')

@section('title', 'Thêm loại hồ sơ')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Loại hồ sơ', 'url' => route('document-types.index')], ['label' => 'Thêm mới']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        <div>
            <p class="page-eyebrow">Danh mục mới</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Thêm loại hồ sơ</h1>
            <p class="mt-2 text-sm text-slate-500">Mã loại là duy nhất và không thể thay đổi sau khi tạo.</p>
        </div>

        <form method="POST" action="{{ route('document-types.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('document-types.partials.form', ['mode' => 'create', 'documentType' => null])
        </form>
    </div>
@endsection
