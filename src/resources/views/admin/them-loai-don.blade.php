@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-loai-don') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if ($errors->any())
  <div class="thong-bao loi" style="max-width:650px;">⚠ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:650px;">
  <form method="POST" action="{{ route('admin.quan-ly-loai-don.luu') }}">
    @csrf

    <div class="form-group">
      <label for="code">Mã loại chứng chỉ</label>
      <input type="text" id="code" name="code" class="form-control" required
             value="{{ old('code') }}" placeholder="VD: THPT, CCNN, CCTH, CCQP">
    </div>

    <div class="form-group">
      <label for="name">Tên loại chứng chỉ</label>
      <input type="text" id="name" name="name" class="form-control" required
             value="{{ old('name') }}" placeholder="VD: Chứng chỉ ngoại ngữ">
    </div>

    <div class="form-group">
      <label for="description">Mô tả</label>
      <textarea id="description" name="description" class="form-control" rows="3"
                placeholder="Mô tả ngắn về loại chứng chỉ này">{{ old('description') }}</textarea>
    </div>

    <div class="form-group">
      <label for="is_active">Trạng thái</label>
      <select id="is_active" name="is_active" class="form-control">
        <option value="1">Hoạt động</option>
        <option value="0">Không hoạt động</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="{{ route('admin.quan-ly-loai-don') }}" class="btn btn-outline">Hủy</a>
      <button type="submit" class="btn btn-primary">Lưu loại đơn</button>
    </div>
  </form>
</div>
@endsection