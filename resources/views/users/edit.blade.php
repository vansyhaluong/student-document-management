@extends('layouts.app')

@section('title', 'Chỉnh sửa người dùng')

@section('breadcrumb')
    <x-breadcrumb :items="[['label' => 'Người dùng', 'url' => route('users.index')], ['label' => 'Chỉnh sửa']]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <div>
            <p class="page-eyebrow">Tài khoản {{ $managedUser['username'] }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Chỉnh sửa người dùng</h1>
            <p class="mt-2 text-sm text-slate-500">Username được giữ nguyên sau khi tài khoản được tạo.</p>
        </div>

        <form method="POST" action="{{ route('users.update', $managedUser['id']) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('users.partials.form', ['mode' => 'edit'])
        </form>

        <section class="mt-6 rounded-xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-100 pb-5">
                <h2 class="text-base font-semibold text-slate-900">Đặt lại mật khẩu</h2>
                <p class="mt-1 text-sm text-slate-500">Nhập trực tiếp mật khẩu mới, tối thiểu 8 ký tự.</p>
            </div>
            <form method="POST" action="{{ route('users.password', $managedUser['id']) }}" class="mt-5 grid gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')
                <x-form-field name="password" label="Mật khẩu mới" type="password" autocomplete="new-password" required />
                <x-form-field name="password_confirmation" label="Xác nhận mật khẩu mới" type="password" autocomplete="new-password" required />
                <div class="md:col-span-2">
                    <button type="submit" class="btn-secondary border-amber-300 text-amber-800 hover:border-amber-400 hover:bg-amber-50">Đặt lại mật khẩu</button>
                </div>
            </form>
        </section>
    </div>
@endsection
