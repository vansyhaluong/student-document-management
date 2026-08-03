@extends('layouts.admin')

@section('content')
<div class="hang-loc-thoi-gian" style="display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
  <a href="?so_ngay=7" class="btn {{ $days === 7 ? 'btn-primary' : 'btn-outline' }}">7 ngày qua</a>
  <a href="?so_ngay=30" class="btn {{ $days === 30 ? 'btn-primary' : 'btn-outline' }}">30 ngày qua</a>
  <a href="?so_ngay=90" class="btn {{ $days === 90 ? 'btn-primary' : 'btn-outline' }}">90 ngày qua</a>
  <a href="{{ route('admin.bao-cao.xuat') }}" class="btn btn-success" style="margin-left:auto;">⬇ Xuất báo cáo (CSV)</a>
</div>

<div class="hang-the-thong-ke">
  @foreach ($statusChart as $item)
    <div class="card the-thong-ke">
      <p class="nhan">{{ $item['status']->label() }}</p>
      <p class="so-lieu">{{ number_format($item['count'], 0, ',', '.') }}</p>
      <p class="ty-le">{{ $item['percent'] }}%</p>
    </div>
  @endforeach
</div>

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

<div class="card">
  <div class="tieu-de-card"><span>Thống kê đơn theo thời gian ({{ $days }} ngày qua)</span></div>

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
    @endforeach
  </svg>

  <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--chu-phu); padding: 0 20px;">
    @foreach ($dateList as $idx => $d)
      @if ($idx % $labelStep === 0 || $idx === $days - 1)
        <span>{{ \Carbon\Carbon::parse($d)->format('d/m') }}</span>
      @endif
    @endforeach
  </div>
</div>
@endsection