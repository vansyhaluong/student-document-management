@extends('layouts.admin')

@section('content')
<div class="card">
  <form method="GET" action="{{ route('admin.quan-ly-don') }}" class="hang-tim-kiem">
    <input type="text" name="tu_khoa" class="form-control o-tim-kiem"
           placeholder="Tìm theo mã đơn, MSSV, tên sinh viên..."
           value="{{ $keyword }}">

    <select name="status" class="form-control">
      <option value="">Tất cả trạng thái</option>
      @foreach ($allStatuses as $s)
        <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
      @endforeach
    </select>

    <input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}">
    <input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}">

    <button type="submit" class="btn btn-outline">Lọc</button>
  </form>

  <table>
    <thead>
      <tr><th>STT</th><th>Mã đơn</th><th>Sinh viên</th><th>MSSV</th><th>Loại chứng chỉ</th><th>Ngày nộp</th><th>Trạng thái</th><th></th></tr>
    </thead>
    <tbody>
      @forelse ($documents as $doc)
        <tr>
          <td>{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
          <td>{{ $doc->document_code }}</td>
          <td>{{ $doc->student->full_name }}</td>
          <td>{{ $doc->student_code }}</td>
          <td>{{ $doc->documentType->name }}</td>
          <td>{{ $doc->submitted_at?->format('d/m/Y H:i') }}</td>
          <td><span class="badge {{ $doc->status->badgeClass() }}">{{ $doc->status->label() }}</span></td>
          <td><a href="{{ route('admin.quan-ly-don.chi-tiet', $doc->id) }}" class="btn btn-outline" style="padding:6px 12px;">👁 Xem</a></td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center;color:var(--chu-phu);">Không có đơn nào phù hợp.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="phan-trang" style="margin-top:14px;">
    {{ $documents->links() }}
  </div>

  <p style="color:var(--chu-phu); font-size:13px; margin-top:10px;">
    Hiển thị {{ $documents->count() }} / {{ $documents->total() }} đơn (tổng toàn hệ thống: {{ $totalAll }})
  </p>
</div>
@endsection