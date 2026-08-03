@extends('layouts.admin')

@section('content')
<div class="hang-the-thong-ke">
  @foreach (array_slice($statusChart, 0, 5) as $item)
    <div class="card the-thong-ke">
      <p class="nhan">{{ $item['status']->label() }}</p>
      <p class="so-lieu">{{ number_format($item['count'], 0, ',', '.') }}</p>
      <p class="ty-le">{{ $item['percent'] }}%</p>
    </div>
  @endforeach
</div>

<div class="hang-2-cot" style="margin-bottom:18px;">
  <div class="card">
    <div class="tieu-de-card"><span>Thống kê đơn theo trạng thái</span></div>
    <div class="khoi-bieu-do">
      <div class="bieu-do-tron" style="background: conic-gradient({{ $gradientString }});">
        <div class="lo-trong">
          <span class="so">{{ number_format($totalDocuments, 0, ',', '.') }}</span>
          <span class="nhan">Tổng số đơn</span>
        </div>
      </div>
      <div class="chu-thich-bieu-do">
        @foreach ($statusChart as $item)
          <div class="dong">
            <span class="ten"><span class="cham" style="background:{{ $item['color'] }};"></span> {{ $item['status']->label() }}</span>
            <span>{{ number_format($item['count'], 0, ',', '.') }} ({{ $item['percent'] }}%)</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="card">
    <div class="tieu-de-card"><span>Loại chứng chỉ phổ biến</span></div>
    <div class="danh-sach-xep-hang">
      @foreach ($popularTypes as $i => $type)
        <div class="dong">
          <span class="so-thu-tu">{{ $i + 1 }}</span>
          <span class="ten-loai">{{ $type->name }}</span>
          <span class="so-luong">{{ number_format($type->documents_count, 0, ',', '.') }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="hang-2-cot">
  <div class="card" style="flex: 2;">
    <div class="tieu-de-card"><span>Đơn hồ sơ mới nhất</span></div>
    <table>
      <thead><tr><th>Mã đơn</th><th>Sinh viên</th><th>Loại chứng chỉ</th><th>Ngày nộp</th><th>Trạng thái</th></tr></thead>
      <tbody>
        @forelse ($latestDocuments as $doc)
          <tr>
            <td>{{ $doc->document_code }}</td>
            <td>{{ $doc->student->full_name }}</td>
            <td>{{ $doc->documentType->name }}</td>
            <td>{{ $doc->submitted_at?->format('d/m/Y H:i') }}</td>
            <td><span class="badge {{ $doc->status->badgeClass() }}">{{ $doc->status->label() }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:var(--chu-phu);">Chưa có đơn nào.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card" style="flex: 1;">
    <div class="tieu-de-card"><span>Hoạt động gần đây</span></div>
    <div class="hoat-dong-gan-day">
      @forelse ($recentActivities as $act)
        <div class="dong">
          <div class="icon-tron">📝</div>
          <div class="noi-dung">
            <p class="mo-ta">{{ $act->description }}</p>
            <p class="thoi-gian">{{ $act->created_at?->format('d/m/Y H:i') }}</p>
          </div>
        </div>
      @empty
        <p style="color:var(--chu-phu); font-size:13px;">Chưa có hoạt động nào.</p>
      @endforelse
    </div>
  </div>
</div>
@endsection