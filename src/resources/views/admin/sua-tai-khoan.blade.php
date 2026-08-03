@extends('layouts.admin')

@section('content')
<a href="{{ route('admin.quan-ly-tai-khoan') }}" class="btn btn-outline" style="margin-bottom:18px;">← Quay lại danh sách</a>

@if ($errors->any())
  <div class="thong-bao loi" style="max-width:650px;">⚠ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:650px;">
  <form method="POST" action="{{ route('admin.quan-ly-tai-khoan.cap-nhat', $account->id) }}">
    @csrf

    <div class="form-group">
      <label for="full_name">Họ và tên</label>
      <input type="text" id="full_name" name="full_name" class="form-control" required
             value="{{ old('full_name', $account->full_name) }}">
    </div>

    <div class="form-group">
      <label>Username</label>
      <input type="text" class="form-control" value="{{ $account->username }}" disabled
             style="background:#f3f4f6; color:var(--chu-phu);">
      <p style="font-size:12px; color:var(--chu-phu); margin-top:4px;">Không thể thay đổi username sau khi tạo.</p>
    </div>

    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" class="form-control" required
             value="{{ old('email', $account->email) }}">
    </div>

    <div class="form-group">
      <label for="password">Mật khẩu mới (để trống nếu không đổi)</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới nếu muốn đổi">
    </div>

    <div class="form-group">
      <label for="role">Vai trò</label>
      <select id="role" name="role" class="form-control" required>
        @foreach ($roles as $r)
          <option value="{{ $r }}" @selected(old('role', $account->role) === $r)>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="is_active">Trạng thái</label>
      <select id="is_active" name="is_active" class="form-control">
        <option value="1" @selected(old('is_active', (int) $account->is_active) == 1)>Hoạt động</option>
        <option value="0" @selected(old('is_active', (int) $account->is_active) == 0)>Đã khóa</option>
      </select>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
      <a href="{{ route('admin.quan-ly-tai-khoan') }}" class="btn btn-outline">Hủy</a>
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
    </div>
  </form>
</div>
@endsection