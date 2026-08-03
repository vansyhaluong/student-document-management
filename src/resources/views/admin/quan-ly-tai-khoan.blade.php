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
  <form method="GET" action="{{ route('admin.quan-ly-tai-khoan') }}" class="hang-tim-kiem">
    <input type="text" name="tu_khoa" class="form-control o-tim-kiem"
           placeholder="Tìm theo tên, email, username..." value="{{ $keyword }}">

    <select name="role" class="form-control">
      <option value="">Tất cả vai trò</option>
      @foreach ($roles as $r)
        <option value="{{ $r }}" @selected($role === $r)>{{ ucfirst($r) }}</option>
      @endforeach
    </select>

    <button type="submit" class="btn btn-outline">Lọc</button>
    <a href="{{ route('admin.quan-ly-tai-khoan.them') }}" class="btn btn-primary" style="margin-left:auto;">+ Thêm tài khoản</a>
  </form>

  <table>
    <thead>
      <tr><th>STT</th><th>Họ và tên</th><th>Username</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
      @forelse ($accounts as $i => $acc)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $acc->full_name }}</td>
          <td>{{ $acc->username }}</td>
          <td>{{ $acc->email }}</td>
          <td>{{ ucfirst($acc->role) }}</td>
          <td>
            @if ($acc->is_active)
              <span class="badge badge-green">Hoạt động</span>
            @else
              <span class="badge badge-red">Đã khóa</span>
            @endif
          </td>
          <td>
            <div class="icon-thao-tac">
              <a href="{{ route('admin.quan-ly-tai-khoan.sua', $acc->id) }}" title="Sửa">✏️</a>
              @if ($acc->id !== auth()->id())
                <form method="POST" action="{{ route('admin.quan-ly-tai-khoan.doi-trang-thai', $acc->id) }}" style="display:inline;"
                      onsubmit="return confirm('Bạn có chắc muốn {{ $acc->is_active ? 'khóa' : 'mở khóa' }} tài khoản này?');">
                  @csrf
                  <button type="submit" style="background:none;border:none;cursor:pointer;font-size:15px;" title="{{ $acc->is_active ? 'Khóa' : 'Mở khóa' }}">
                    {{ $acc->is_active ? '🔒' : '🔓' }}
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center;color:var(--chu-phu);">Không có tài khoản nào phù hợp.</td></tr>
      @endforelse
    </tbody>
  </table>
  <p style="color:var(--chu-phu); font-size:13px; margin-top:10px;">Hiển thị {{ $accounts->count() }} tài khoản</p>
</div>
@endsection