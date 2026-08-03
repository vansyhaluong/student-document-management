@extends('layouts.public')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sinhvien.css') }}">
@endpush

@section('content')
<div class="khung-noi-dung">
  <a href="{{ route('tra-cuu', ['student_code' => $studentCode]) }}" class="btn btn-outline" style="margin-bottom:16px;">← Quay lại danh sách</a>

  @if (!$document)
    <div class="thong-bao loi">⚠ Không tìm thấy đơn phù hợp.</div>
  @else
    <div class="card" style="margin-bottom:20px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="color:var(--xanh-dam);">Đơn {{ $document->document_code }}</h2>
        <span class="badge {{ $document->status->badgeClass() }}">{{ $document->status->label() }}</span>
      </div>

      <div class="thong-tin-sv" style="margin-bottom:20px;">
        <div><span class="nhan">Họ và tên</span><span class="gia-tri">{{ $document->student->full_name }}</span></div>
        <div><span class="nhan">MSSV</span><span class="gia-tri">{{ $document->student->student_code }}</span></div>
        <div><span class="nhan">Loại chứng chỉ</span><span class="gia-tri">{{ $document->documentType->name }}</span></div>
        <div><span class="nhan">Ngày nộp</span><span class="gia-tri">{{ $document->submitted_at?->format('d/m/Y H:i') }}</span></div>
      </div>

      <div class="form-group">
        <label>Nội dung / Lý do</label>
        <p style="white-space:pre-line;">{{ $document->note }}</p>
      </div>

      @if ($document->invalid_reason)
        <div class="form-group">
          <label>Lý do không hợp lệ</label>
          <p>{{ $document->invalid_reason }}</p>
        </div>
      @endif
    </div>

    {{-- Lịch sử xử lý dạng dòng thời gian --}}
    <div class="card">
      <h3 style="color:var(--xanh-dam); margin-bottom:14px;">Lịch sử xử lý</h3>
      <table>
        <thead><tr><th>Thời gian</th><th>Trạng thái</th><th>Người xử lý</th><th>Ghi chú</th></tr></thead>
        <tbody>
          @foreach ($document->statusHistory as $h)
            <tr>
              <td>{{ $h->changed_at?->format('d/m/Y H:i') }}</td>
              <td><span class="badge {{ $h->status->badgeClass() }}">{{ $h->status->label() }}</span></td>
              <td>{{ $h->changedByUser->full_name ?? 'Hệ thống' }}</td>
              <td>{{ $h->note }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection