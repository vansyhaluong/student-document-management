@php
    $tieuDeTrang = 'Trang chủ - Hệ thống tra cứu & nộp đơn sinh viên';
    $nutHeader = ['text' => 'Đăng nhập', 'url' => route('login')];
@endphp
@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/trangchu.css') }}">
@endpush

@section('content')

  {{-- ===== PHẦN GIỚI THIỆU (HERO) ===== --}}
  <section class="hero">
    <h1>HỆ THỐNG TRA CỨU &amp; NỘP ĐƠN SINH VIÊN</h1>
    <p>
      Hỗ trợ sinh viên tra cứu thông tin, nộp hồ sơ trực tuyến
      và theo dõi tình trạng xử lý hồ sơ một cách nhanh chóng, thuận tiện.
    </p>
  </section>

  {{-- ===== 2 THẺ CHỨC NĂNG CHÍNH: NỘP HỒ SƠ và TRA CỨU HỒ SƠ ===== --}}
  <section class="khoi-chuc-nang">

    <div class="the-chuc-nang nop-ho-so">
      <h2>NỘP HỒ SƠ</h2>
      <p class="mo-ta">Nộp hồ sơ trực tuyến nhanh chóng với các loại đơn được hỗ trợ.</p>
      <ul>
        <li>Xác thực thông tin sinh viên</li>
        <li>Chọn loại đơn cần nộp</li>
        <li>Điền thông tin và gửi hồ sơ</li>
        <li>Theo dõi tình trạng xử lý</li>
        <li>Nhận thông báo qua email</li>
      </ul>
      <a href="{{ route('nop-don') }}" class="btn btn-primary">NỘP HỒ SƠ NGAY →</a>
    </div>

    <div class="chu-hoac">HOẶC</div>

    <div class="the-chuc-nang tra-cuu">
      <h2>TRA CỨU HỒ SƠ</h2>
      <p class="mo-ta">Tra cứu và theo dõi tình trạng xử lý các hồ sơ đã nộp.</p>
      <ul>
        <li>Xem danh sách hồ sơ đã nộp</li>
        <li>Theo dõi tình trạng xử lý</li>
        <li>Xem chi tiết và ghi chú xử lý</li>
        <li>Nhận thông báo qua email</li>
      </ul>
      <a href="{{ route('tra-cuu') }}" class="btn btn-success">TRA CỨU NGAY →</a>
    </div>

  </section>

  {{-- ===== THANH LƯU Ý QUAN TRỌNG ===== --}}
  <section class="thanh-luu-y">
    <div class="tieu-de">🔔 LƯU Ý QUAN TRỌNG</div>
    <div class="muc">👤 Sinh viên cần nhập đúng MSSV để xác thực thông tin.</div>
    <div class="muc">✉️ Vui lòng cung cấp email chính xác để nhận thông báo kết quả xử lý hồ sơ.</div>
    <div class="muc">🕐 Thời gian xử lý tùy thuộc vào từng loại hồ sơ. Vui lòng theo dõi thường xuyên.</div>
  </section>

@endsection
