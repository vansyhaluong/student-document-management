@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sinhvien.css') }}">
@endpush

@section('content')
<div class="khung-noi-dung rong">
  <h1 class="tieu-de-trang">TRA CỨU HỒ SƠ</h1>

  {{-- Form nhập MSSV để tra cứu, dùng GET để giữ lại trong đường dẫn --}}
  <div class="card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('tra-cuu') }}" style="display:flex; gap:12px;">
      <input type="text" name="student_code" class="form-control" placeholder="Nhập MSSV của bạn"
             required value="{{ $studentCode }}">
      <button type="submit" class="btn btn-success">Tra cứu</button>
    </form>
  </div>

  @if ($notFound)
    <div class="thong-bao loi">⚠ Không tìm thấy sinh viên với MSSV này.</div>
  @endif

  @if ($student)
    {{-- Thông tin sinh viên vừa tra cứu --}}
    <div class="thong-tin-sv">
      <div><span class="nhan">Họ và tên</span><span class="gia-tri">{{ $student->full_name }}</span></div>
      <div><span class="nhan">MSSV</span><span class="gia-tri">{{ $student->student_code }}</span></div>
    </div>

    <div class="card">
      {{-- Bộ lọc tìm kiếm đơn: giữ nguyên MSSV bằng input ẩn --}}
      <form method="GET" action="{{ route('tra-cuu') }}" class="hang-bo-loc">
        <input type="hidden" name="student_code" value="{{ $studentCode }}">

        <select name="document_type_id" class="form-control">
          <option value="">Tất cả loại chứng chỉ</option>
          @foreach ($documentTypes as $type)
            <option value="{{ $type->id }}" @selected(request('document_type_id') == $type->id)>
              {{ $type->name }}
            </option>
          @endforeach
        </select>

        <select name="status" class="form-control">
          <option value="">Tất cả trạng thái</option>
          @foreach ($allStatuses as $statusCase)
            <option value="{{ $statusCase->value }}" @selected(request('status') === $statusCase->value)>
              {{ $statusCase->label() }}
            </option>
          @endforeach
        </select>

        <input type="date" name="tu_ngay" class="form-control" value="{{ request('tu_ngay') }}">
        <input type="date" name="den_ngay" class="form-control" value="{{ request('den_ngay') }}">

        <button type="submit" class="btn btn-outline">Lọc</button>
      </form>

      {{-- Bảng danh sách đơn đã nộp --}}
      <table>
        <thead>
          <tr>
            <th>STT</th><th>Mã đơn</th><th>Loại chứng chỉ</th><th>Ngày nộp</th><th>Trạng thái</th><th>Ghi chú</th><th></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($documents as $doc)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $doc->document_code }}</td>
              <td>{{ $doc->documentType->name }}</td>
              <td>{{ $doc->submitted_at?->format('d/m/Y H:i') }}</td>
              <td><span class="badge {{ $doc->status->badgeClass() }}">{{ $doc->status->label() }}</span></td>
              <td>{{ $doc->note }}</td>
              <td>
                <a href="{{ route('tra-cuu.chi-tiet', ['document_code' => $doc->document_code, 'student_code' => $studentCode]) }}"
                   class="btn btn-outline" style="padding:6px 12px;">Xem</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" style="text-align:center; color:var(--chu-phu);">Không có đơn nào phù hợp.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection