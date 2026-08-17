@extends('layouts.guest')

@section('title', 'Đăng nhập')

@section('content')
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-md">
            <section class="rounded-xl border border-blue-100 bg-white p-6 shadow-sm sm:p-7">
                <a href="{{ route('home') }}" class="mb-5 block rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-faculty-600">
                    <img src="{{ asset('images/tdc-logo.png') }}" alt="Khoa Công nghệ Thông tin" class="mx-auto h-auto w-full max-w-[16.5rem] object-contain">
                </a>

                <div class="mb-6 text-center">
                    <p class="page-eyebrow">Cổng cán bộ nội bộ</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Đăng nhập hệ thống</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Nhập tài khoản đã được cấp để tiếp tục làm việc.</p>
                </div>

                <x-flash />

                <form method="POST" action="{{ route('login.store') }}" class="space-y-4" novalidate>
                    @csrf

                    <x-form-field
                        name="username"
                        label="Tên đăng nhập"
                        :value="old('username')"
                        autocomplete="username"
                        required
                        autofocus
                    />

                    <x-form-field
                        name="password"
                        label="Mật khẩu"
                        type="password"
                        autocomplete="current-password"
                        required
                    />

                    <button type="submit" class="btn-primary group mt-1 w-full">
                        <span>Đăng nhập</span>
                        <svg aria-hidden="true" class="size-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6" /></svg>
                    </button>
                </form>
            </section>

            <p class="mt-4 text-center text-xs leading-5 text-slate-500">
                Chỉ dành cho cán bộ Khoa Công nghệ Thông tin.
                <a href="{{ route('home') }}" class="font-semibold text-faculty-800 hover:text-faculty-900">Về trang chủ</a>
            </p>
        </div>
    </main>
@endsection
