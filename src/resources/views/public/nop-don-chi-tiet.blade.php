@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sinhvien.css') }}">
@endpush

@section('content')
<div class="khung-noi-dung">
  <h1 class="tieu-de-trang">NỘP HỒ SƠ TRỰC TUYẾN</h1>

  {{-- Chỉ báo bước hiện tại: đã xong bước 1, đang ở bước 2 --}}
  <div class="chi-bao-buoc">
    <div class="buoc da-xong"><span class="so">✓</span> Xác thực MSSV</div>
    <div class="buoc dang-active"><span class="so">2</span> Điền thông tin đơn</div>
    <div class="buoc"><span class="so">3</span> Hoàn tất</div>
  </div>

  {{-- Thông tin sinh viên đã xác thực, chỉ hiển thị, không cho sửa --}}
  <div class="thong-tin-sv">
    <div><span class="nhan">Họ và tên</span><span class="gia-tri">{{ $student->full_name }}</span></div>
    <div><span class="nhan">MSSV</span><span class="gia-tri">{{ $student->student_code }}</span></div>
    <div><span class="nhan">Ngày sinh</span><span class="gia-tri">{{ $student->date_of_birth?->format('d/m/Y') }}</span></div>
  </div>

  <div class="card">
    @if ($errors->any())
      <div class="thong-bao loi">⚠ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('nop-don.chi-tiet.submit') }}">
      @csrf

      <div class="form-group">
        <label for="document_type_id">Loại chứng chỉ cần nộp</label>
        <select id="document_type_id" name="document_type_id" class="form-control" required>
          <option value="">-- Chọn loại chứng chỉ --</option>
          @foreach ($documentTypes as $type)
            <option value="{{ $type->id }}" @selected(old('document_type_id') == $type->id)>
              {{ $type->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="form-group">
        <label for="note">Nội dung / Lý do nộp</label>
        <textarea id="note" name="note" class="form-control" rows="4"
                  placeholder="Trình bày lý do, nội dung đề nghị..." required>{{ old('note') }}</textarea>
      </div>

      <div style="display:flex; gap:12px;">
        <a href="{{ route('nop-don') }}" class="btn btn-outline" style="flex:1; justify-content:center;">← Quay lại</a>
        <button type="submit" class="btn btn-primary" style="flex:2; justify-content:center;">Gửi đơn →</button>
      </div>
    </form>
  </div>
</div>
@endsection