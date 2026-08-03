@extends('layouts.admin')

@section('content')
@if (session('thanh_cong'))
  <div class="thong-bao" style="background:#dcfce7; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
    ✓ {{ session('thanh_cong') }}
  </div>
@endif

<div class="card">
  <form method="GET" action="{{ route('admin.quan-ly-loai-don') }}" class="hang-tim-kiem">
    <input type="text" name="tu_khoa" class="form-control o-tim-kiem"
           placeholder="Tìm theo mã hoặc tên loại chứng chỉ..." value="{{ $keyword }}">
    <button type="submit" class="btn btn-outline">Tìm</button>
    <a href="{{ route('admin.quan-ly-loai-don.them') }}" class="btn btn-primary" style="margin-left:auto;">+ Thêm loại đơn</a>
  </form>

  <table>
    <thead>
      <tr><th>STT</th><th>Mã</th><th>Tên loại chứng chỉ</th><th>Mô tả</th><th>Trạng thái</th><th>Thao tác</th></tr>
    </thead>
    <tbody>
      @forelse ($documentTypes as $i => $type)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><b>{{ $type->code }}</b></td>
          <td>{{ $type->name }}</td>
          <td>{{ $type->description ?? '' }}</td>
          <td>
            <form method="POST" action="{{ route('admin.quan-ly-loai-don.doi-trang-thai', $type->id) }}" style="display:inline;">
              @csrf
              <label class="cong-tac">
                <input type="checkbox" onchange="this.form.submit()" @checked($type->is_active)>
                <span class="thanh-truot"></span>
              </label>
            </form>
          </td>
          <td>
            <div class="icon-thao-tac">
              <a href="{{ route('admin.quan-ly-loai-don.sua', $type->id) }}" title="Sửa loại đơn">✏️</a>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--chu-phu);">Chưa có loại chứng chỉ nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  <p style="color:var(--chu-phu); font-size:13px; margin-top:10px;">Hiển thị {{ $documentTypes->count() }} loại chứng chỉ</p>
</div>
@endsection