<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStatus;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->query('so_ngay', 7);
        if (! in_array($days, [7, 30, 90], true)) {
            $days = 7;
        }

        $totalAll = StudentDocument::count();
        $countsByStatus = StudentDocument::selectRaw('status, COUNT(*) as total')
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

        $rows = StudentDocument::selectRaw('DATE(submitted_at) as ngay, status, COUNT(*) as so_luong')
            ->where('submitted_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('ngay', 'status')
            ->get();

        $dateList = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateList[] = now()->subDays($i)->format('Y-m-d');
        }

        $seriesByStatus = [];
        foreach (DocumentStatus::cases() as $status) {
            foreach ($dateList as $d) {
                $seriesByStatus[$status->value][$d] = 0;
            }
        }
        foreach ($rows as $row) {
            $seriesByStatus[$row->status->value][$row->ngay] = (int) $row->so_luong;
        }

        $maxValue = 1;
        foreach ($seriesByStatus as $byDate) {
            foreach ($byDate as $v) {
                $maxValue = max($maxValue, $v);
            }
        }

        // Vẽ SVG line chart
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
        foreach (DocumentStatus::cases() as $status) {
            $points = [];
            foreach ($dateList as $idx => $d) {
                $x = $marginLeft + $idx * $stepX;
                $value = $seriesByStatus[$status->value][$d];
                $y = $marginTop + $drawHeight - ($value / $maxValue * $drawHeight);
                $points[] = round($x, 1).','.round($y, 1);
            }
            $lines[$status->value] = implode(' ', $points);
        }

        $labelStep = max(1, (int) ceil($days / 7));

        return view('admin.bao-cao-thong-ke', [
            'tieuDeTrang' => 'Báo cáo - Thống kê',
            'trangHienTai' => 'bao_cao',
            'days' => $days,
            'totalAll' => $totalAll,
            'statusChart' => $statusChart,
            'gradientString' => $gradientString,
            'dateList' => $dateList,
            'lines' => $lines,
            'svgWidth' => $svgWidth,
            'svgHeight' => $svgHeight,
            'marginLeft' => $marginLeft,
            'marginTop' => $marginTop,
            'drawHeight' => $drawHeight,
            'labelStep' => $labelStep,
        ]);
    }

    public function export(Request $request)
    {
        $documents = StudentDocument::with(['student', 'documentType'])
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
}
