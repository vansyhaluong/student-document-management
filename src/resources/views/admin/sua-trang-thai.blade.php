@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-trang-thai') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if ($errors->any())
  <div class="thong-bao loi" style="max-width:650px;">⚠ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:650px;">
  <form method="POST" action="{{ route('admin.quan-ly-trang-thai.cap-nhat', $status->id) }}">
    @csrf

    <div class="form-group">
      <label for="code">Mã trạng thái</label>
      @if ($status->is_system)
        <input type="text" class="form-control" value="{{ $status->code }}" disabled
               style="background:#f3f4f6; color:var(--chu-phu);">
        <p style="font-size:12px; color:var(--chu-phu); margin:6px 0 0;">🔒 Trạng thái hệ thống — không thể đổi mã.</p>
      @else
        <input type="text" id="code" name="code" class="form-control" required
               value="{{ old('code', $status->code) }}">
      @endif
    </div>

    <div class="form-group">
      <label for="label">Tên hiển thị</label>
      <input type="text" id="label" name="label" class="form-control" required
             value="{{ old('label', $status->label) }}">
    </div>

    <div class="form-group">
      <label for="badge_class">Màu nhãn (badge)</label>
      <select id="badge_class" name="badge_class" class="form-control">
        @foreach ($badgeClassOptions as $value => $text)
          <option value="{{ $value }}" @selected(old('badge_class', $status->badge_class) === $value)>{{ $text }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="color_hex">Màu biểu đồ (mã hex)</label>
      <input type="color" id="color_hex" name="color_hex" class="form-control" style="height:44px; padding:4px;"
             value="{{ old('color_hex', $status->color_hex) }}">
    </div>

    <div class="form-group">
      <label for="sort_order">Thứ tự hiển thị</label>
      <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
             value="{{ old('sort_order', $status->sort_order) }}">
    </div>

    <div class="form-group">
      <label for="is_active">Trạng thái</label>
      <select id="is_active" name="is_active" class="form-control">
        <option value="1" @selected(old('is_active', (int) $status->is_active) == 1)>Hoạt động</option>
        <option value="0" @selected(old('is_active', (int) $status->is_active) == 0)>Không hoạt động</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="{{ route('admin.quan-ly-trang-thai') }}" class="btn btn-outline">Hủy</a>
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </div>
  </form>
</div>
@endsection