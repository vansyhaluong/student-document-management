@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-don') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if (session('thanh_cong'))
  <div class="thong-bao" style="background:#dcfce7; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
    ✓ {{ session('thanh_cong') }}
  </div>
@endif

<div class="khung-2-cot-chitiet">
  <div class="cot-trai">
    <div class="card">
      <div class="tieu-de-card">
        <span>Thông tin đơn</span>
        <span class="badge {{ $document->status->badgeClass() }}">{{ $document->status->label() }}</span>
      </div>

      <p class="nhan-thong-tin">Mã đơn</p><p class="gia-tri-thong-tin">{{ $document->document_code }}</p>
      <p class="nhan-thong-tin">Loại chứng chỉ</p><p class="gia-tri-thong-tin">{{ $document->documentType->name }}</p>
      <p class="nhan-thong-tin">Sinh viên</p><p class="gia-tri-thong-tin">{{ $document->student->full_name }}</p>
      <p class="nhan-thong-tin">MSSV</p><p class="gia-tri-thong-tin">{{ $document->student_code }}</p>
      <p class="nhan-thong-tin">Ngày nộp</p><p class="gia-tri-thong-tin">{{ $document->submitted_at?->format('d/m/Y H:i') }}</p>

      <p class="nhan-thong-tin">Nội dung / Ghi chú hiện tại</p>
      <p style="white-space:pre-line; margin-bottom:14px;">{{ $document->note }}</p>

      @if ($document->invalid_reason)
        <p class="nhan-thong-tin">Lý do không hợp lệ</p>
        <p style="margin-bottom:14px;">{{ $document->invalid_reason }}</p>
      @endif
    </div>
  </div>

  <div class="cot-phai">
    <div class="card">
      <div class="tieu-de-card"><span>Lịch sử xử lý</span></div>
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

    <div class="card">
      <div class="tieu-de-card"><span>Cập nhật trạng thái</span></div>

      @if (!$canUpdate)
        <div class="huong-dan-box">
          Bạn không có quyền cập nhật trạng thái cho đơn này ở thời điểm hiện tại.
          @if (auth()->user()->role === 'staff')
            Vui lòng liên hệ Thư ký để tiếp tục xử lý.
          @endif
        </div>
      @else
        @if ($errors->any())
          <div class="thong-bao loi">⚠ {{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.quan-ly-don.cap-nhat', $document->id) }}" id="form-cap-nhat"
              onsubmit="return xacNhanCapNhat()">
          @csrf
          <div class="form-group">
            <label for="status_moi">Trạng thái mới</label>
                <select id="status_moi" name="status_moi" class="form-control" required
                    onchange="capNhatFormTheoTrangThai(this)">
              @foreach ($allowedStatuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group" id="khung-ly-do-invalid" style="display:none;">
            <label for="invalid_reason">Lý do không hợp lệ</label>
            <input type="text" id="invalid_reason" name="invalid_reason" class="form-control"
                   placeholder="Chỉ nhập khi chọn trạng thái Không hợp lệ">
          </div>

          <div class="form-group">
            <label for="ghi_chu_moi" id="nhan-ghi-chu">Ghi chú</label>
            <textarea id="ghi_chu_moi" name="ghi_chu_moi" class="form-control" rows="3"
                      placeholder="Nhập ghi chú xử lý..."></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Cập nhật</button>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
function capNhatFormTheoTrangThai(select) {
  document.getElementById('khung-ly-do-invalid').style.display = (select.value === 'invalid') ? 'block' : 'none';

  const canGhiChu = (select.value === 'invalid' || select.value === 'cancelled');
  const ghiChu = document.getElementById('ghi_chu_moi');
  const nhan = document.getElementById('nhan-ghi-chu');

  if (canGhiChu) {
    ghiChu.setAttribute('required', 'required');
    nhan.textContent = 'Ghi chú (bắt buộc — dùng để thông báo cho sinh viên)';
  } else {
    ghiChu.removeAttribute('required');
    nhan.textContent = 'Ghi chú (không bắt buộc)';
  }
}

function xacNhanCapNhat() {
  const select = document.getElementById('status_moi');
  const nhan = select.options[select.selectedIndex].text;
  return confirm(`Xác nhận chuyển đơn sang trạng thái "${nhan}"?`);
}

document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('status_moi');
  if (select) capNhatFormTheoTrangThai(select);
});
</script>
@endpush