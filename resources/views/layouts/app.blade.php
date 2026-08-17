<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Tổng quan') · {{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-blue-50/60 font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[18rem_1fr]">
            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/55 lg:hidden" aria-hidden="true"></div>

            <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-ink-950 text-blue-50 shadow-xl transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shadow-none" aria-label="Điều hướng ứng dụng">
                <div class="border-b border-white/10 px-5 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('dashboard') }}" class="min-w-0 rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                            <p class="text-sm font-semibold text-white">Hệ thống quản lý hồ sơ</p>
                            <p class="mt-0.5 text-xs text-blue-200/70">Cổng nghiệp vụ nội bộ</p>
                        </a>
                        <button id="sidebar-close" type="button" class="grid size-9 shrink-0 place-items-center rounded-lg text-blue-100 transition hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-blue-300 lg:hidden">
                            <span class="sr-only">Đóng menu</span>
                            <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg>
                        </button>
                    </div>
                </div>

                <nav aria-label="Điều hướng chính" class="mt-5 flex-1 overflow-y-auto px-4 pb-6">
                    <p class="px-3 text-[0.68rem] font-semibold tracking-[0.14em] text-blue-200/55 uppercase">Nghiệp vụ</p>

                    <div class="mt-2 space-y-1.5">
                        <a href="{{ route('dashboard') }}" @class([
                            'nav-item',
                            'nav-item-active' => request()->routeIs('dashboard'),
                        ])>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z" /></svg>
                            <span>Tổng quan</span>
                        </a>

                        <a href="{{ route('documents.index') }}" @class([
                            'nav-item',
                            'nav-item-active' => request()->routeIs('documents.*'),
                        ])>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h10l4 4v14H5V3Z" /><path d="M14 3v5h5M8 12h8M8 16h6" /></svg>
                            <span>Hồ sơ</span>
                        </a>

                        @if (auth()->user()->hasRole(\App\Enums\UserRole::ADMIN))
                            <a href="{{ route('document-types.index') }}" @class([
                                'nav-item',
                                'nav-item-active' => request()->routeIs('document-types.*'),
                            ])>
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v5H4V5Zm0 9h7v5H4v-5Zm11 0h5v5h-5v-5Z" /></svg>
                                <span>Loại hồ sơ</span>
                            </a>
                            <a href="{{ route('users.index') }}" @class([
                                'nav-item',
                                'nav-item-active' => request()->routeIs('users.*'),
                            ])>
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 20v-1.5A3.5 3.5 0 0 0 12.5 15h-5A3.5 3.5 0 0 0 4 18.5V20M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 8h4M19 6v4" /></svg>
                                <span>Người dùng</span>
                            </a>
                        @endif

                        @can('view-reports')
                            <a href="{{ route('reports.index') }}" @class([
                                'nav-item',
                                'nav-item-active' => request()->routeIs('reports.*'),
                            ])>
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 20V10M12 20V4M19 20v-7" /></svg>
                                <span>Báo cáo</span>
                            </a>
                        @endcan

                        @can('view-activity-log')
                            <a href="{{ route('activity-log.index') }}" @class([
                                'nav-item',
                                'nav-item-active' => request()->routeIs('activity-log.*'),
                            ])>
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5l3 2M4.9 4.9a10 10 0 1 1-1.8 11.6" /><path d="M3 10V4h6" /></svg>
                                <span>Nhật ký hoạt động</span>
                            </a>
                        @endcan
                    </div>
                </nav>

                <div class="border-t border-white/10 p-4">
                    <div class="flex items-center gap-3 rounded-lg bg-white/7 px-3 py-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-faculty-500 text-sm font-bold text-ink-950">
                            {{ mb_strtoupper(mb_substr(auth()->user()->full_name, 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-white">{{ auth()->user()->full_name }}</span>
                            <span class="block text-xs text-blue-200/70">{{ auth()->user()->role->label() }}</span>
                        </span>
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-20 flex h-17 items-center border-b border-blue-100 bg-white px-4 sm:px-6 lg:px-8">
                    <button id="sidebar-toggle" type="button" class="mr-3 grid size-10 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-faculty-600 lg:hidden" aria-controls="app-sidebar" aria-expanded="false">
                        <span class="sr-only">Mở menu</span>
                        <svg aria-hidden="true" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        @yield('breadcrumb')
                    </div>

                    <div class="ml-4 flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="max-w-52 truncate text-sm font-semibold text-slate-800">{{ auth()->user()->full_name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->role->label() }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-secondary px-3.5">
                                <svg aria-hidden="true" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M10 17l5-5-5-5M15 12H3M14 4h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5" /></svg>
                                <span class="hidden sm:inline">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-7">
                    <x-flash />
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
