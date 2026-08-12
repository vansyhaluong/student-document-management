<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Báo cáo thống kê</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    h1 { font-size: 18px; margin-bottom: 4px; }
    .phu-de { color: #6b7280; margin-bottom: 20px; font-size: 11px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
    th { background: #f9fafb; }
    .khung-mau { display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:6px; }
    .tong-so { font-size: 14px; font-weight: bold; margin-bottom: 12px; }
  </style>
</head>
<body>
  <h1>Báo cáo - Thống kê hồ sơ</h1>
  <p class="phu-de">Khoảng thời gian: {{ $tuNgayHienThi }} — {{ $denNgayHienThi }} &nbsp;|&nbsp; Xuất lúc: {{ $ngayXuat }}</p>

  <p class="tong-so">Tổng số đơn: {{ number_format($totalAll, 0, ',', '.') }}</p>

  <table>
    <thead>
      <tr>
        <th>Trạng thái</th>
        <th>Số lượng</th>
        <th>Tỷ lệ</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($statusChart as $item)
        <tr>
          <td><span class="khung-mau" style="background: {{ $item['color'] }};"></span>{{ $item['status']->label() }}</td>
          <td>{{ number_format($item['count'], 0, ',', '.') }}</td>
          <td>{{ $item['percent'] }}%</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table>
    <thead>
      <tr>
        <th>Mã đơn</th>
        <th>MSSV</th>
        <th>Họ tên</th>
        <th>Loại chứng chỉ</th>
        <th>Ngày nộp</th>
        <th>Trạng thái</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($documents as $doc)
        <tr>
          <td>{{ $doc->document_code }}</td>
          <td>{{ $doc->student_code }}</td>
          <td>{{ $doc->student->full_name ?? '' }}</td>
          <td>{{ $doc->documentType->name ?? '' }}</td>
          <td>{{ $doc->submitted_at?->format('d/m/Y H:i') }}</td>
          <td>{{ $doc->status->label() }}</td>
        </tr>
      @empty
        <tr><td colspan="6">Không có dữ liệu trong khoảng thời gian này.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
