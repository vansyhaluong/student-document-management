@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-tai-khoan') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if ($errors->any())
  <div class="thong-bao loi" style="max-width:650px;">⚠ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:650px;">
  <form method="POST" action="{{ route('admin.quan-ly-tai-khoan.luu') }}">
    @csrf

    <div class="form-group">
      <label for="full_name">Họ và tên</label>
      <input type="text" id="full_name" name="full_name" class="form-control" required value="{{ old('full_name') }}">
    </div>

    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" required value="{{ old('username') }}">
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" class="form-control" required value="{{ old('email') }}">
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu</label>
      <input type="password" id="password" name="password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
    </div>

    <div class="form-group">
      <label for="role">Vai trò</label>
      <select id="role" name="role" class="form-control" required>
        <option value="">-- Chọn vai trò --</option>
        @foreach ($roles as $r)
          <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="is_active">Trạng thái</label>
      <select id="is_active" name="is_active" class="form-control">
        <option value="1">Hoạt động</option>
        <option value="0">Đã khóa</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="{{ route('admin.quan-ly-tai-khoan') }}" class="btn btn-outline">Hủy</a>
      <button type="submit" class="btn btn-primary">Lưu tài khoản</button>
    </div>
  </form>
</div>
@endsection