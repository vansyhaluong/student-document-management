@extends('layouts.admin')

@section('content')

<style>
  .hang-loc-thoi-gian { display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap; align-items:center; }
  .nhom-nut-ngay { display:flex; gap:10px; flex-wrap:wrap; }
  .loc-loai-don select {
    padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; background:#fff;
    font-size:14px; min-width:180px;
  }
  .nhom-xuat { display:flex; gap:10px; margin-left:auto; }

  .the-so-sanh {
    display:flex; align-items:baseline; gap:12px; flex-wrap:wrap;
    margin-bottom:18px; padding:14px 18px; background:#fff; border-radius:10px;
    border:1px solid #eef0f3;
  }
  .the-so-sanh .so-lon { font-size:26px; font-weight:700; color:#1f2937; }
  .badge-so-sanh {
    font-size:13px; font-weight:600; padding:3px 10px; border-radius:999px;
  }
  .badge-tang { background:#e6f7ee; color:#0f9d58; }
  .badge-giam { background:#fdecea; color:#d93025; }

  .hang-the-thong-ke {
    display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap:14px; margin-bottom:18px;
  }
  .the-thong-ke {
    background:#fff; border-radius:10px; padding:16px;
    border-left:4px solid var(--mau-the, #999);
    box-shadow:0 1px 2px rgba(0,0,0,0.04);
    display:flex; flex-direction:column; gap:6px;
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .the-thong-ke:hover { transform: translateY(-2px); box-shadow:0 4px 10px rgba(0,0,0,0.08); }
  .the-thong-ke .icon-the {
    width:28px; height:28px; color: var(--mau-the, #999);
  }
  .the-thong-ke .nhan { font-size:13px; color:#6b7280; margin:0; }
  .the-thong-ke .so-lieu { font-size:24px; font-weight:700; margin:0; color:#1f2937; }
  .the-thong-ke .ty-le { font-size:12px; color:#9ca3af; margin:0; }

  .bang-du-lieu { width:100%; border-collapse:collapse; font-size:14px; }
  .bang-du-lieu th, .bang-du-lieu td { text-align:left; padding:10px 8px; border-bottom:1px solid #f0f1f3; }
  .bang-du-lieu th { color:#6b7280; font-weight:600; font-size:13px; }

  .diem-du-lieu circle { cursor:pointer; }
  .diem-du-lieu circle:hover { r: 5; }

  @media (max-width: 640px) {
    .nhom-xuat { margin-left:0; width:100%; }
    .the-so-sanh { flex-direction:column; align-items:flex-start; }
  }
</style>

{{-- Bộ lọc thời gian nhanh + loại đơn + xuất báo cáo --}}
<div class="hang-loc-thoi-gian">
  <div class="nhom-nut-ngay">
    <a href="{{ route('admin.bao-cao', ['so_ngay' => 7, 'loai_don' => $documentTypeId]) }}" class="btn {{ (! $customRange && $days === 7) ? 'btn-primary' : 'btn-outline' }}">7 ngày qua</a>
    <a href="{{ route('admin.bao-cao', ['so_ngay' => 30, 'loai_don' => $documentTypeId]) }}" class="btn {{ (! $customRange && $days === 30) ? 'btn-primary' : 'btn-outline' }}">30 ngày qua</a>
    <a href="{{ route('admin.bao-cao', ['so_ngay' => 90, 'loai_don' => $documentTypeId]) }}" class="btn {{ (! $customRange && $days === 90) ? 'btn-primary' : 'btn-outline' }}">90 ngày qua</a>
  </div>

  <form method="GET" action="{{ route('admin.bao-cao') }}" class="loc-loai-don">
    <input type="hidden" name="so_ngay" value="{{ $days }}">
    <select name="loai_don" onchange="this.form.submit()">
      <option value="">Tất cả loại đơn</option>
      @foreach ($documentTypes as $type)
        <option value="{{ $type->id }}" {{ (string) $documentTypeId === (string) $type->id ? 'selected' : '' }}>
          {{ $type->name }}
        </option>
      @endforeach
    </select>
  </form>

  <div class="nhom-xuat">
    <a href="{{ route('admin.bao-cao.xuat', ['loai_don' => $documentTypeId]) }}" class="btn btn-success">⬇ CSV</a>
    <a href="{{ route('admin.bao-cao.xuat-pdf', ['so_ngay' => $days, 'tu_ngay' => $tuNgay, 'den_ngay' => $denNgay, 'loai_don' => $documentTypeId]) }}" class="btn btn-danger">⬇ PDF</a>
  </div>
</div>

{{-- Lọc theo khoảng ngày tùy chỉnh (giống trang Lịch sử thao tác) --}}
<div class="card" style="margin-bottom:18px;">
  <form method="GET" action="{{ route('admin.bao-cao') }}" class="hang-tim-kiem">
    <input type="hidden" name="loai_don" value="{{ $documentTypeId }}">
    <label style="font-size:13px; color:#6b7280; align-self:center;">Từ ngày</label>
    <input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}">
    <label style="font-size:13px; color:#6b7280; align-self:center;">Đến ngày</label>
    <input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}">
    <button type="submit" class="btn btn-outline">Lọc</button>
    @if ($customRange)
      <a href="{{ route('admin.bao-cao', ['so_ngay' => 7, 'loai_don' => $documentTypeId]) }}" class="btn btn-outline">Bỏ lọc ngày</a>
    @endif
  </form>
  @if ($groupByWeek)
    <p style="font-size:12px; color:#9ca3af; margin-top:10px; margin-bottom:0;">
      Khoảng thời gian dài ({{ $periodDays }} ngày) — biểu đồ đường bên dưới được gộp theo tuần để dễ theo dõi.
    </p>
  @endif
</div>

{{-- So sánh với kỳ trước --}}
<div class="the-so-sanh">
  <span class="so-lon">{{ number_format($currentTotal, 0, ',', '.') }}</span>
  <span>đơn @if ($customRange) từ {{ \Carbon\Carbon::parse($tuNgay)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($denNgay)->format('d/m/Y') }} @else trong {{ $days }} ngày qua @endif</span>
  @if ($changePercent !== null)
    <span class="badge-so-sanh {{ $changePercent >= 0 ? 'badge-tang' : 'badge-giam' }}">
      {{ $changePercent >= 0 ? '▲' : '▼' }} {{ number_format(abs($changePercent), 1) }}% so với kỳ trước đó (cùng độ dài)
    </span>
  @else
    <span class="badge-so-sanh" style="background:#f3f4f6; color:#6b7280;">Chưa có dữ liệu kỳ trước để so sánh</span>
  @endif
</div>

{{-- Thẻ thống kê theo trạng thái --}}
<div class="hang-the-thong-ke">
  @foreach ($statusChart as $item)
    <div class="the-thong-ke" style="--mau-the: {{ $item['color'] }};">
      <svg class="icon-the" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
        <polyline points="14 2 14 8 20 8"></polyline>
        <path d="m9 15 2 2 4-4"></path>
      </svg>
      <p class="nhan">{{ $item['status']->label() }}</p>
      <p class="so-lieu">{{ number_format($item['count'], 0, ',', '.') }}</p>
      <p class="ty-le">{{ $item['percent'] }}%</p>
    </div>
  @endforeach
</div>

{{-- Biểu đồ tròn theo trạng thái --}}
<div class="card" style="margin-bottom:18px;">
  <div class="tieu-de-card"><span>Thống kê đơn theo trạng thái</span></div>
  <div class="khoi-bieu-do">
    <div class="bieu-do-tron" style="background: conic-gradient({{ $gradientString }});">
      <div class="lo-trong">
        <span class="so">{{ number_format($totalAll, 0, ',', '.') }}</span>
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

{{-- Biểu đồ đường theo thời gian --}}
<div class="card" style="margin-bottom:18px;">
  <div class="tieu-de-card">
    <span>Thống kê đơn theo thời gian
      @if ($customRange)
        ({{ \Carbon\Carbon::parse($tuNgay)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($denNgay)->format('d/m/Y') }})
      @else
        ({{ $days }} ngày qua)
      @endif
    </span>
  </div>

  <div class="chu-thich-duong" style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:14px; font-size:12px;">
    @foreach ($statusChart as $item)
      <span style="display:flex; align-items:center; gap:6px;">
        <span style="width:14px; height:3px; background:{{ $item['color'] }}; display:inline-block;"></span>
        {{ $item['status']->label() }}
      </span>
    @endforeach
  </div>

  <svg viewBox="0 0 {{ $svgWidth }} {{ $svgHeight }}" width="100%">
    @for ($m = 0; $m <= 2; $m++)
      @php $y = $marginTop + $drawHeight * $m / 2; @endphp
      <line x1="{{ $marginLeft }}" y1="{{ $y }}" x2="{{ $svgWidth - 20 }}" y2="{{ $y }}" stroke="#e5e7eb" stroke-width="1"/>
    @endfor

    @foreach ($statusChart as $item)
      <polyline points="{{ $lines[$item['status']->value] }}" fill="none" stroke="{{ $item['color'] }}" stroke-width="2.5" />
      <g class="diem-du-lieu">
        @foreach ($points[$item['status']->value] as $p)
          <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="{{ $item['color'] }}">
            <title>{{ \Carbon\Carbon::parse($p['date'])->format('d/m/Y') }} — {{ $item['status']->label() }}: {{ $p['value'] }}</title>
          </circle>
        @endforeach
      </g>
    @endforeach
  </svg>

  <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--chu-phu); padding: 0 20px;">
    @foreach ($dateList as $idx => $d)
      @if ($idx % $labelStep === 0 || $idx === count($dateList) - 1)
        <span>{{ $groupByWeek ? 'T.'.\Carbon\Carbon::parse($d)->format('d/m') : \Carbon\Carbon::parse($d)->format('d/m') }}</span>
      @endif
    @endforeach
  </div>
</div>

{{-- Top loại đơn được nộp nhiều nhất --}}
<div class="card">
  <div class="tieu-de-card">
    <span>Top loại đơn được nộp nhiều nhất
      @if ($customRange)
        ({{ \Carbon\Carbon::parse($tuNgay)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($denNgay)->format('d/m/Y') }})
      @else
        ({{ $days }} ngày qua)
      @endif
    </span>
  </div>
  <table class="bang-du-lieu">
    <thead>
      <tr>
        <th>Loại chứng chỉ</th>
        <th>Số lượng đơn</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($popularTypes as $type)
        <tr>
          <td>{{ $type->name }}</td>
          <td>{{ number_format($type->documents_count, 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr><td colspan="2" style="color:#9ca3af;">Chưa có dữ liệu trong khoảng thời gian này.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
