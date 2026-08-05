@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-trang-thai') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if ($errors->any())
  <div class="thong-bao loi" style="max-width:650px;">⚠ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:650px;">
  <form method="POST" action="{{ route('admin.quan-ly-trang-thai.luu') }}">
    @csrf

    <div class="form-group">
      <label for="code">Mã trạng thái (chữ thường, không dấu, không khoảng trắng)</label>
      <input type="text" id="code" name="code" class="form-control" required
             value="{{ old('code') }}" placeholder="VD: cho_ky_duyet">
    </div>

    <div class="form-group">
      <label for="label">Tên hiển thị</label>
      <input type="text" id="label" name="label" class="form-control" required
             value="{{ old('label') }}" placeholder="VD: Chờ ký duyệt">
    </div>

    <div class="form-group">
      <label for="badge_class">Màu nhãn (badge)</label>
      <select id="badge_class" name="badge_class" class="form-control">
        @foreach ($badgeClassOptions as $value => $text)
          <option value="{{ $value }}" @selected(old('badge_class') === $value)>{{ $text }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="color_hex">Màu biểu đồ (mã hex)</label>
      <input type="color" id="color_hex" name="color_hex" class="form-control" style="height:44px; padding:4px;"
             value="{{ old('color_hex', '#1e4fd6') }}">
    </div>

    <div class="form-group">
      <label for="sort_order">Thứ tự hiển thị</label>
      <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
             value="{{ old('sort_order', $nextSortOrder) }}">
    </div>

    <div class="form-group">
      <label for="is_active">Trạng thái</label>
      <select id="is_active" name="is_active" class="form-control">
        <option value="1">Hoạt động</option>
        <option value="0">Không hoạt động</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="{{ route('admin.quan-ly-trang-thai') }}" class="btn btn-outline">Hủy</a>
      <button type="submit" class="btn btn-primary">Lưu trạng thái</button>
    </div>
  </form>
</div>
@endsection