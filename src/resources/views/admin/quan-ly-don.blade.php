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
      <tr>
        <th>STT</th><th>Mã đơn</th><th>Sinh viên</th><th>MSSV</th><th>Loại chứng chỉ</th>
        <th>Ngày nộp</th><th>Trạng thái</th><th>Người tiếp nhận</th><th></th>
      </tr>
    </thead>
    <tbody>
      @forelse ($documents as $doc)
        @php [$canUpdate, $allowed] = $permissions[$doc->id]; @endphp
        <tr>
          <td>{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
          <td>{{ $doc->document_code }}</td>
          <td>{{ $doc->student->full_name }}</td>
          <td>{{ $doc->student_code }}</td>
          <td>{{ $doc->documentType->name }}</td>
          <td>{{ $doc->submitted_at?->format('d/m/Y H:i') }}</td>

          <td>
            @if ($canUpdate)
              <form method="POST" action="{{ route('admin.quan-ly-don.cap-nhat', $doc->id) }}">
                @csrf
                <input type="hidden" name="ghi_chu_moi" value="">
                <select name="status_moi" class="form-control badge-select {{ $doc->status->badgeClass() }}"
                        data-current-value="{{ $doc->status->value }}"
                        style="padding:4px 8px; font-size:12.5px; font-weight:600; border-radius:20px;"
                        onchange="xuLyDoiTrangThai(this)">
                  @foreach ($allowed as $s)
                    <option value="{{ $s->value }}" @selected($doc->status->value === $s->value)>{{ $s->label() }}</option>
                  @endforeach
                </select>
              </form>
            @else
              <span class="badge {{ $doc->status->badgeClass() }}">{{ $doc->status->label() }}</span>
            @endif
          </td>

          <td>{{ $doc->assignedSecretary->full_name ?? '—' }}</td>

          <td><a href="{{ route('admin.quan-ly-don.chi-tiet', $doc->id) }}" class="btn btn-outline" style="padding:6px 12px;">Xem</a></td>
        </tr>
      @empty
        <tr><td colspan="9" style="text-align:center;color:var(--chu-phu);">Không có đơn nào phù hợp.</td></tr>
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

@push('scripts')
<script>
function xuLyDoiTrangThai(select) {
  const trangThaiMoi = select.value;
  const nhanTrangThai = select.options[select.selectedIndex].text;
  const canGhiChu = (trangThaiMoi === 'invalid' || trangThaiMoi === 'cancelled');

  if (!confirm(`Xác nhận chuyển đơn sang trạng thái "${nhanTrangThai}"?`)) {
    select.value = select.dataset.currentValue;
    return;
  }

  let ghiChu = '';
  if (canGhiChu) {
    ghiChu = prompt(`Nhập lý do "${nhanTrangThai}" (sẽ dùng để thông báo cho sinh viên):`);
    if (ghiChu === null || ghiChu.trim() === '') {
      alert('Bắt buộc nhập lý do khi chuyển sang trạng thái này.');
      select.value = select.dataset.currentValue;
      return;
    }
  }

  const form = select.closest('form');
  form.querySelector('input[name="ghi_chu_moi"]').value = ghiChu;
  form.submit();
}
</script>
@endpush
@endsection