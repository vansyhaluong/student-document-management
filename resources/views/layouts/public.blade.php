<!DOCTYPE html>
<html lang="vi" class="public-html scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Hệ thống quản lý hồ sơ sinh viên') · {{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-portal-50 font-sans text-portal-ink antialiased">
        <a href="#student-services-title" class="public-skip-link">Bỏ qua nội dung điều hướng</a>
        @yield('content')
    </body>
</html>
