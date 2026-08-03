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

  {{-- ===== HEADER: logo bên trái + nút hành động bên phải ===== --}}
  <header class="public-header">
    <div class="logo-box">
      <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
      <div class="logo-text">
        <p>KHOA CÔNG NGHỆ THÔNG TIN</p>
        <p>TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THỦ ĐỨC</p>
      </div>
    </div>
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
