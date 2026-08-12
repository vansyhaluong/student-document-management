<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStatus;
use App\Models\DocumentType;
use App\Models\StudentDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $documentTypeId = $request->query('loai_don');
        $tuNgay = $request->query('tu_ngay', '');
        $denNgay = $request->query('den_ngay', '');

        // ===== Xác định khoảng thời gian: ưu tiên lọc tùy chỉnh (tu_ngay/den_ngay),
        // nếu không có thì dùng 3 nút nhanh 7/30/90 ngày =====
        $customRange = $tuNgay !== '' && $denNgay !== '';
        $days = null;

        if ($customRange) {
            try {
                $startDate = \Carbon\Carbon::parse($tuNgay)->startOfDay();
                $endDate = \Carbon\Carbon::parse($denNgay)->endOfDay();
                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }
            } catch (\Exception $e) {
                $customRange = false;
            }
        }

        if (! $customRange) {
            $days = (int) $request->query('so_ngay', 7);
            if (! in_array($days, [7, 30, 90], true)) {
                $days = 7;
            }
            $startDate = now()->subDays($days - 1)->startOfDay();
            $endDate = now()->endOfDay();
        }

        $periodDays = $startDate->diffInDays($endDate) + 1;
        // Nếu khoảng quá dài (>120 ngày), gộp biểu đồ theo tuần để tránh quá tải
        $groupByWeek = $periodDays > 120;

        $baseQuery = fn () => StudentDocument::query()
            ->when($documentTypeId, fn ($q) => $q->where('document_type_id', $documentTypeId));

        $totalAll = $baseQuery()->count();
        $countsByStatus = $baseQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChart = [];
        $cumulative = 0;
        foreach (DocumentStatus::cases() as $status) {
            $count = (int) ($countsByStatus[$status->value] ?? 0);
            $percent = $totalAll > 0 ? round($count / $totalAll * 100, 1) : 0;
            $start = $cumulative;
            $cumulative += $percent;
            $statusChart[] = [
                'status' => $status,
                'color' => $status->color_hex,
                'count' => $count,
                'percent' => $percent,
                'gradient' => "{$status->color_hex} {$start}% {$cumulative}%",
            ];
        }
        $gradientString = implode(', ', array_column($statusChart, 'gradient'));

        // ===== Dữ liệu theo thời gian (kỳ hiện tại) =====
        $rows = $baseQuery()
            ->selectRaw('DATE(submitted_at) as ngay, status, COUNT(*) as so_luong')
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->groupBy('ngay', 'status')
            ->get();

        $dateList = [];
        if ($groupByWeek) {
            $cursor = $startDate->copy()->startOfWeek();
            $lastWeekStart = $endDate->copy()->startOfWeek();
            while ($cursor->lte($lastWeekStart)) {
                $dateList[] = $cursor->format('Y-m-d');
                $cursor->addWeek();
            }
        } else {
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dateList[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
        }

        $seriesByStatus = [];
        foreach (DocumentStatus::cases() as $status) {
            foreach ($dateList as $d) {
                $seriesByStatus[$status->value][$d] = 0;
            }
        }
        foreach ($rows as $row) {
            $bucket = $groupByWeek
                ? \Carbon\Carbon::parse($row->ngay)->startOfWeek()->format('Y-m-d')
                : $row->ngay;
            if (isset($seriesByStatus[$row->status->value][$bucket])) {
                $seriesByStatus[$row->status->value][$bucket] += (int) $row->so_luong;
            }
        }

        $maxValue = 1;
        foreach ($seriesByStatus as $byDate) {
            foreach ($byDate as $v) {
                $maxValue = max($maxValue, $v);
            }
        }

        // ===== Vẽ SVG line chart (giữ nguyên cơ chế cũ, thêm toạ độ điểm cho tooltip) =====
        $svgWidth = 720;
        $svgHeight = 240;
        $marginLeft = 30;
        $marginBottom = 30;
        $marginTop = 20;
        $drawWidth = $svgWidth - $marginLeft - 20;
        $drawHeight = $svgHeight - $marginTop - $marginBottom;
        $pointCount = count($dateList);
        $stepX = $pointCount > 1 ? $drawWidth / ($pointCount - 1) : 0;

        $lines = [];
        $points = [];
        foreach (DocumentStatus::cases() as $status) {
            $pts = [];
            $ptList = [];
            foreach ($dateList as $idx => $d) {
                $x = $marginLeft + $idx * $stepX;
                $value = $seriesByStatus[$status->value][$d];
                $y = $marginTop + $drawHeight - ($value / $maxValue * $drawHeight);
                $pts[] = round($x, 1).','.round($y, 1);
                $ptList[] = [
                    'x' => round($x, 1),
                    'y' => round($y, 1),
                    'value' => $value,
                    'date' => $d,
                ];
            }
            $lines[$status->value] = implode(' ', $pts);
            $points[$status->value] = $ptList;
        }

        $pointCountForLabels = count($dateList);
        $labelStep = max(1, (int) ceil($pointCountForLabels / 7));

        // ===== So sánh với kỳ trước (cùng độ dài ngày, ngay trước kỳ hiện tại) =====
        $prevEnd = $startDate->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($periodDays - 1)->startOfDay();

        $prevTotal = $baseQuery()
            ->whereBetween('submitted_at', [$prevStart, $prevEnd])
            ->count();

        $currentTotal = $baseQuery()
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->count();

        $changePercent = null;
        if ($prevTotal > 0) {
            $changePercent = round((($currentTotal - $prevTotal) / $prevTotal) * 100, 1);
        } elseif ($currentTotal > 0) {
            $changePercent = 100.0;
        }

        // ===== Top loại đơn được nộp nhiều nhất trong kỳ đang xem =====
        $popularTypes = DocumentType::withCount(['documents' => function ($q) use ($startDate, $endDate, $documentTypeId) {
                $q->whereBetween('submitted_at', [$startDate, $endDate]);
                if ($documentTypeId) {
                    $q->where('document_type_id', $documentTypeId);
                }
            }])
            ->orderByDesc('documents_count')
            ->take(5)
            ->get();

        $documentTypes = DocumentType::orderBy('name')->get();

        return view('admin.bao-cao-thong-ke', [
            'tieuDeTrang' => 'Báo cáo - Thống kê',
            'trangHienTai' => 'bao_cao',
            'days' => $days,
            'customRange' => $customRange,
            'tuNgay' => $customRange ? $tuNgay : '',
            'denNgay' => $customRange ? $denNgay : '',
            'groupByWeek' => $groupByWeek,
            'periodDays' => $periodDays,
            'totalAll' => $totalAll,
            'statusChart' => $statusChart,
            'gradientString' => $gradientString,
            'dateList' => $dateList,
            'lines' => $lines,
            'points' => $points,
            'svgWidth' => $svgWidth,
            'svgHeight' => $svgHeight,
            'marginLeft' => $marginLeft,
            'marginTop' => $marginTop,
            'drawHeight' => $drawHeight,
            'labelStep' => $labelStep,
            'currentTotal' => $currentTotal,
            'prevTotal' => $prevTotal,
            'changePercent' => $changePercent,
            'popularTypes' => $popularTypes,
            'documentTypes' => $documentTypes,
            'documentTypeId' => $documentTypeId,
        ]);
    }

    public function export(Request $request)
    {
        $documentTypeId = $request->query('loai_don');

        $documents = StudentDocument::with(['student', 'documentType'])
            ->when($documentTypeId, fn ($q) => $q->where('document_type_id', $documentTypeId))
            ->orderByDesc('submitted_at')
            ->get();

        $filename = 'bao-cao-don-'.now()->format('Ymd_His').'.csv';

        return Response::streamDownload(function () use ($documents) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Mã đơn', 'MSSV', 'Họ tên', 'Loại chứng chỉ', 'Ngày nộp', 'Trạng thái', 'Ghi chú']);

            foreach ($documents as $doc) {
                fputcsv($handle, [
                    $doc->document_code,
                    $doc->student_code,
                    $doc->student->full_name ?? '',
                    $doc->documentType->name ?? '',
                    $doc->submitted_at?->format('d/m/Y H:i'),
                    $doc->status->label(),
                    $doc->note,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $documentTypeId = $request->query('loai_don');
        $tuNgay = $request->query('tu_ngay', '');
        $denNgay = $request->query('den_ngay', '');

        $customRange = $tuNgay !== '' && $denNgay !== '';
        if ($customRange) {
            try {
                $startDate = \Carbon\Carbon::parse($tuNgay)->startOfDay();
                $endDate = \Carbon\Carbon::parse($denNgay)->endOfDay();
                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
                }
            } catch (\Exception $e) {
                $customRange = false;
            }
        }

        if (! $customRange) {
            $days = (int) $request->query('so_ngay', 7);
            if (! in_array($days, [7, 30, 90], true)) {
                $days = 7;
            }
            $startDate = now()->subDays($days - 1)->startOfDay();
            $endDate = now()->endOfDay();
        }

        $baseQuery = fn () => StudentDocument::query()
            ->when($documentTypeId, fn ($q) => $q->where('document_type_id', $documentTypeId));

        $totalAll = $baseQuery()->count();
        $countsByStatus = $baseQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChart = [];
        foreach (DocumentStatus::cases() as $status) {
            $count = (int) ($countsByStatus[$status->value] ?? 0);
            $percent = $totalAll > 0 ? round($count / $totalAll * 100, 1) : 0;
            $statusChart[] = [
                'status' => $status,
                'color' => $status->color_hex,
                'count' => $count,
                'percent' => $percent,
            ];
        }

        $documents = $baseQuery()
            ->with(['student', 'documentType'])
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->orderByDesc('submitted_at')
            ->get();

        $pdf = Pdf::loadView('admin.bao-cao-pdf', [
            'tuNgayHienThi' => $startDate->format('d/m/Y'),
            'denNgayHienThi' => $endDate->format('d/m/Y'),
            'totalAll' => $totalAll,
            'statusChart' => $statusChart,
            'documents' => $documents,
            'ngayXuat' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'bao-cao-thong-ke-'.now()->format('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }
}
