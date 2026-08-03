@extends('layouts.admin')

@section('content')
<div class="card">
  <form method="GET" action="{{ route('admin.lich-su') }}" class="hang-tim-kiem">
    @if (auth()->user()->role === 'admin')
      <select name="nguoi_dung_id" class="form-control">
        <option value="">Tất cả cán bộ</option>
        @foreach ($accounts as $acc)
          <option value="{{ $acc->id }}" @selected($causerId == $acc->id)>{{ $acc->full_name }}</option>
        @endforeach
      </select>
    @endif

    <input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}">
    <input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}">

    <button type="submit" class="btn btn-outline">Lọc</button>
  </form>

  <table>
    <thead><tr><th>Thời gian</th><th>Người thực hiện</th><th>Mô tả</th></tr></thead>
    <tbody>
      @forelse ($activities as $act)
        <tr>
          <td>{{ $act->created_at?->format('d/m/Y H:i') }}</td>
          <td>{{ $act->causer->full_name ?? 'Hệ thống/Sinh viên' }}</td>
          <td>{{ $act->description }}</td>
        </tr>
      @empty
        <tr><td colspan="3" style="text-align:center;color:var(--chu-phu);">Chưa có nhật ký nào.</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="phan-trang" style="margin-top:14px;">
    {{ $activities->links() }}
  </div>
</div>
@endsection