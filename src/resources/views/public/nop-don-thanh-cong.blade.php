@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sinhvien.css') }}">
@endpush

@section('content')
<div class="khung-noi-dung">
  {{-- Chỉ báo bước hiện tại: đã hoàn tất cả 3 bước --}}
  <div class="chi-bao-buoc">
    <div class="buoc da-xong"><span class="so">✓</span> Xác thực MSSV</div>
    <div class="buoc da-xong"><span class="so">✓</span> Điền thông tin đơn</div>
    <div class="buoc dang-active"><span class="so">✓</span> Hoàn tất</div>
  </div>

  <div class="card ket-qua-thanh-cong">
    <div class="icon">✅</div>
    <h2 style="color: var(--xanh-la);">Gửi đơn thành công!</h2>
    <p style="color: var(--chu-phu); margin-top:8px;">
      Đơn của bạn đã được ghi nhận với mã đơn bên dưới. Vui lòng lưu lại để tra cứu tình trạng xử lý.
    </p>
    <div class="ma-don">{{ $documentCode }}</div>
    <div class="nhom-nut">
      <a href="{{ route('tra-cuu') }}" class="btn btn-success">Tra cứu tình trạng đơn</a>
      <a href="{{ route('home') }}" class="btn btn-outline">Về trang chủ</a>
    </div>
  </div>
</div>
@endsection