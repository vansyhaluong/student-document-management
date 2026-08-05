@extends('layouts.admin')

@section('content')
@if (session('thanh_cong'))
  <div class="thong-bao" style="background:#dcfce7; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
    ✓ {{ session('thanh_cong') }}
  </div>
@endif
@if (session('loi'))
  <div class="thong-bao loi">⚠ {{ session('loi') }}</div>
@endif

<div class="card">
  <div class="hang-tim-kiem">
    <p style="margin:0; color:var(--chu-phu); font-size:13px;">
      Trạng thái hệ thống (khóa 🔒) không thể đổi mã hoặc xóa, chỉ sửa được tên/màu/thứ tự.
    </p>
    <a href="{{ route('admin.quan-ly-trang-thai.them') }}" class="btn btn-primary" style="margin-left:auto;">+ Thêm trạng thái</a>
  </div>

  <table>
    <thead>
      <tr><th>Thứ tự</th><th>Mã</th><th>Tên hiển thị</th><th>Xem trước</th><th>Số đơn đang dùng</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
      @forelse ($statuses as $s)
        <tr>
          <td>{{ $s->sort_order }}</td>
          <td><b>{{ $s->code }}</b> @if ($s->is_system) 🔒 @endif</td>
          <td>{{ $s->label }}</td>
          <td><span class="badge {{ $s->badge_class }}">{{ $s->label }}</span></td>
          <td>{{ $countsByStatus[$s->code] ?? 0 }}</td>
          <td>
            <form method="POST" action="{{ route('admin.quan-ly-trang-thai.doi-trang-thai', $s->id) }}" style="display:inline;">
              @csrf
              <label class="cong-tac">
                <input type="checkbox" onchange="this.form.submit()" @checked($s->is_active)>
                <span class="thanh-truot"></span>
              </label>
            </form>
          </td>
          <td>
            <div class="icon-thao-tac">
              <a href="{{ route('admin.quan-ly-trang-thai.sua', $s->id) }}" title="Sửa">✏️</a>
              @if (! $s->is_system)
                <form method="POST" action="{{ route('admin.quan-ly-trang-thai.xoa', $s->id) }}"
                      onsubmit="return confirm('Xóa trạng thái \"{{ $s->label }}\"? Hành động này không thể hoàn tác.');" style="display:inline;">
                  @csrf
                  <button type="submit" style="background:none; border:none; cursor:pointer; font-size:15px;" title="Xóa">🗑️</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:var(--chu-phu);">Chưa có trạng thái nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  <p style="color:var(--chu-phu); font-size:13px; margin-top:10px;">Hiển thị {{ $statuses->count() }} trạng thái</p>
</div>
@endsection