<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $tieuDeTrang ?? 'Hệ thống tra cứu & nộp đơn sinh viên' }}</title>

  {{-- CSS dùng chung (nút, badge, form...) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  {{-- CSS riêng cho header/footer công khai --}}
  <link rel="stylesheet" href="{{ asset('assets/css/public.css') }}">

  @stack('styles')
</head>
<body>

  {{-- ===== THANH TIỆN ÍCH MỎNG (liên hệ nhanh) ===== --}}
  <div class="top-utility-bar">
    <div class="lien-he-nhanh">
      <a href="tel:0283896534">📞 (028) 3896 8534</a>
      <a href="mailto:it@tdc.edu.vn">✉ it@tdc.edu.vn</a>
    </div>
    <div class="lien-he-nhanh">
      <span>53 Võ Văn Ngân, TP. Thủ Đức, TP.HCM</span>
    </div>
  </div>

  {{-- ===== THANH CHÍNH: logo + menu + nút hành động ===== --}}
  <header class="public-header">
    <a href="{{ route('home') }}" class="logo-box">
      <img src="{{ asset('assets/images/logo-tdc.png') }}" alt="Logo Khoa CNTT - TDC" onerror="this.style.display='none'">
      <div class="logo-text">
        <p>KHOA CÔNG NGHỆ THÔNG TIN</p>
        <p>TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THỦ ĐỨC</p>
      </div>
    </a>

    <button type="button" class="nut-mo-menu" onclick="document.querySelector('.public-menu').classList.toggle('mo')">☰</button>

    <nav class="public-menu">
      <a href="{{ route('home') }}" class="{{ ($trangHienTaiCong ?? '') === 'trang_chu' ? 'dang-chon' : '' }}">Trang chủ</a>
      <a href="{{ route('nop-don') }}" class="{{ ($trangHienTaiCong ?? '') === 'nop_don' ? 'dang-chon' : '' }}">Nộp hồ sơ</a>
      <a href="{{ route('tra-cuu') }}" class="{{ ($trangHienTaiCong ?? '') === 'tra_cuu' ? 'dang-chon' : '' }}">Tra cứu hồ sơ</a>
    </nav>

    <a href="{{ $nutHeader['url'] }}" class="btn">
      {{ $nutHeader['text'] }}
    </a>
  </header>

  @yield('content')

  {{-- ===== FOOTER: bản quyền bên trái + thông tin liên hệ bên phải ===== --}}
  <footer class="public-footer">
    <p>&copy; 2026 Khoa Công nghệ thông tin - Trường Cao đẳng Công nghệ Thủ Đức. All rights reserved.</p>
    <div class="lien-he">
      <a href="mailto:it@tdc.edu.vn">✉ it@tdc.edu.vn</a>
      <a href="tel:0283896534">📞 (028) 3896 8534</a>
    </div>
  </footer>

  @stack('scripts')
</body>
</html>
