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
        Tổng quan
      </a>
      <a href="{{ route('admin.quan-ly-don') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_don' ? 'dang-chon' : '' }}">
        Quản lý đơn
      </a>
      @if (auth()->user()->role === 'admin')
        <a href="{{ route('admin.quan-ly-loai-don') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_loai_don' ? 'dang-chon' : '' }}">
          Quản lý loại đơn
        </a>
        <a href="{{ route('admin.quan-ly-trang-thai') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_trang_thai' ? 'dang-chon' : '' }}">
          Quản lý trạng thái
        </a>
        <a href="{{ route('admin.quan-ly-tai-khoan') }}" class="{{ ($trangHienTai ?? '') === 'quan_ly_tk' ? 'dang-chon' : '' }}">
          Quản lý tài khoản
        </a>
      @endif
      <a href="{{ route('admin.lich-su') }}" class="{{ ($trangHienTai ?? '') === 'lich_su' ? 'dang-chon' : '' }}">
        Lịch sử thao tác
      </a>
      @if (in_array(auth()->user()->role, ['admin', 'secretary']))
        <a href="{{ route('admin.bao-cao') }}" class="{{ ($trangHienTai ?? '') === 'bao_cao' ? 'dang-chon' : '' }}">
          Báo cáo - Thống kê
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