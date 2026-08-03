@php
    $tieuDeTrang = 'Đăng nhập - Hệ thống tra cứu & nộp đơn sinh viên';
    $nutHeader = ['text' => 'Về trang chủ', 'url' => route('home')];
@endphp
@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
@endpush

@section('content')

<div class="login-wrap">
  <div class="login-box">

    {{-- ===== CỘT TRÁI: giới thiệu + minh họa ===== --}}
    <div class="login-gioi-thieu">
      <h2>HỆ THỐNG TRA CỨU &amp; NỘP ĐƠN SINH VIÊN</h2>
      <p>Hệ thống dành cho cán bộ quản lý.<br>Vui lòng đăng nhập để tiếp tục.</p>
      <div class="icon-khoa">🔐</div>
    </div>

    {{-- ===== CỘT PHẢI: form đăng nhập ===== --}}
    <div class="login-form">
      <h1>ĐĂNG NHẬP HỆ THỐNG</h1>
      <div class="gach-chan"></div>

      @if ($errors->has('login'))
        <div class="login-loi">⚠ {{ $errors->first('login') }}</div>
      @endif

      <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
          <label for="username">Tên đăng nhập</label>
          <input type="text" id="username" name="username" class="form-control"
                 value="{{ old('username') }}"
                 placeholder="Nhập tên đăng nhập" required autofocus>
        </div>

        <div class="form-group">
          <label for="mat-khau">Mật khẩu</label>
          <div style="position:relative;">
            <input type="password" id="mat-khau" name="mat_khau" class="form-control"
                   placeholder="Nhập mật khẩu" required>
            <span id="icon-mat" onclick="hienAnMatKhau()"
                  style="position:absolute; right:14px; top:10px; cursor:pointer;">👁️</span>
          </div>
        </div>

        <div class="login-hang-tuy-chon">
          <label><input type="checkbox" name="ghi_nho"> Ghi nhớ đăng nhập</label>
          <a href="#">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn btn-primary">🔒 Đăng nhập</button>
      </form>

      <div class="login-hoac">Hoặc</div>

      <a href="{{ route('home') }}" class="btn btn-outline">← Quay lại trang chủ</a>

      <div class="login-ghi-chu">
        <span>🛡️</span>
        <span><b>Lưu ý:</b> Chỉ tài khoản có quyền (Admin, Thư ký, Nhân viên) mới có thể đăng nhập.</span>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
  <script src="{{ asset('assets/js/login.js') }}"></script>
@endpush
