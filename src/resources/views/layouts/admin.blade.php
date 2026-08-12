<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $tieuDeTrang ?? 'Quản trị hệ thống' }}</title>

  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  @stack('styles')
</head>
<body class="admin-body">

  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="logo-box">
        <img src="{{ asset('assets/images/logo-tdc.png') }}" alt="Logo TDC" class="logo-img">
        <div class="logo-text">
          <p>KHOA CÔNG NGHỆ THÔNG TIN</p>
          <p class="logo-sub">Trường Cao đẳng Công nghệ Thủ Đức</p>
        </div>
      </div>

      <nav class="menu-admin">
      <a href="{{ route('admin.dashboard') }}" class="{{ ($trangHienTai ?? '') === 'tong_quan' ? 'dang-chon' : '' }}">
        <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
        <span>Tổng quan</span>
      </a>
      <a href="{{ route('admin.quan-ly-don') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_don' ? 'dang-chon' : '' }}">
        <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        <span>Quản lý đơn</span>
      </a>
      @if (auth()->user()->role === 'admin')
        <a href="{{ route('admin.quan-ly-loai-don') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_loai_don' ? 'dang-chon' : '' }}">
          <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2.58 12.58a2 2 0 0 1 0-2.83l7.17-7.17a2 2 0 0 1 2.83 0l7.99 8a2 2 0 0 1 .01 2.83z"></path><line x1="7" y1="7.5" x2="7.01" y2="7.5"></line></svg>
          <span>Quản lý loại đơn</span>
        </a>
        <a href="{{ route('admin.quan-ly-trang-thai') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_trang_thai' ? 'dang-chon' : '' }}">
          <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
          <span>Quản lý trạng thái</span>
        </a>
        <a href="{{ route('admin.quan-ly-tai-khoan') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_tk' ? 'dang-chon' : '' }}">
          <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          <span>Quản lý tài khoản</span>
        </a>
      @endif
      <a href="{{ route('admin.lich-su') }}" class="{{ ($trangHienTai ?? '') === 'lich_su' ? 'dang-chon' : '' }}">
        <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        <span>Lịch sử thao tác</span>
      </a>
      @if (in_array(auth()->user()->role, ['admin', 'secretary']))
        <a href="{{ route('admin.bao-cao') }}" class="{{ ($trangHienTai ?? '') === 'bao_cao' ? 'dang-chon' : '' }}">
          <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
          <span>Báo cáo - Thống kê</span>
        </a>
      @endif
    </nav>
    </aside>

    <div class="admin-main">
      <header class="admin-header">
        <div></div>
        <div class="admin-header-user">
          <span>{{ auth()->user()->full_name }} ({{ auth()->user()->role }})</span>
          <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-outline" style="padding:6px 14px;">Đăng xuất</button>
          </form>
        </div>
      </header>

      <main class="admin-content">
        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
