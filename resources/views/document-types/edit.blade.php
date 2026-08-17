@extends('layouts.app')

@section('title', 'Chỉnh sửa loại hồ sơ')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Loại hồ sơ', 'url' => route('document-types.index')], ['label' => 'Chỉnh sửa']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-3xl">
        <div>
            <p class="page-eyebrow">Mã loại {{ $documentType['code'] }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Chỉnh sửa loại hồ sơ</h1>
            <p class="mt-2 text-sm text-slate-500">Bạn có thể cập nhật tên và mô tả; trạng thái được điều khiển tại danh sách.</p>
        </div>

        <form method="POST" action="{{ route('document-types.update', $documentType['id']) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('document-types.partials.form', ['mode' => 'edit'])
        </form>
    </div>
@endsection
