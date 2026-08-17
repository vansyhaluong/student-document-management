@extends('layouts.app')

@section('title', 'Tạo tài khoản')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Người dùng', 'url' => route('users.index')], ['label' => 'Tạo tài khoản']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <div>
            <p class="page-eyebrow">Người dùng mới</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Tạo tài khoản</h1>
            <p class="mt-2 text-sm text-slate-500">Mật khẩu được thiết lập trực tiếp và không gửi qua email.</p>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('users.partials.form', ['mode' => 'create', 'managedUser' => null])
        </form>
    </div>
@endsection
