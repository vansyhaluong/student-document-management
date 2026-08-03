@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sinhvien.css') }}">
@endpush

@section('content')
<div class="khung-noi-dung">
  <h1 class="tieu-de-trang">NỘP HỒ SƠ TRỰC TUYẾN</h1>

  {{-- Chỉ báo bước hiện tại: đang ở bước 1 --}}
  <div class="chi-bao-buoc">
    <div class="buoc dang-active"><span class="so">1</span> Xác thực MSSV</div>
    <div class="buoc"><span class="so">2</span> Điền thông tin đơn</div>
    <div class="buoc"><span class="so">3</span> Hoàn tất</div>
  </div>

  <div class="card">
    @if ($errors->any())
      <div class="thong-bao loi">⚠ {{ $errors->first('student_code') }}</div>
    @endif

    <form method="POST" action="{{ route('nop-don.submit') }}">
      @csrf
      <div class="form-group">
        <label for="student_code">Mã số sinh viên (MSSV)</label>
        <input type="text" id="student_code" name="student_code" class="form-control"
               placeholder="Nhập MSSV của bạn" required
               value="{{ old('student_code') }}">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
        Tiếp tục →
      </button>
    </form>
  </div>
</div>
@endsection