<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentStatus;
use App\Models\DocumentType;
use App\Models\StudentDocument;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDocuments = StudentDocument::count();
        $countsByStatus = StudentDocument::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChart = [];
        foreach (DocumentStatus::cases() as $status) {
            $statusChart[] = ['status' => $status, 'color' => $status->color_hex];
        }

        $cumulative = 0;
        $gradientParts = [];
        foreach ($statusChart as &$item) {
            $status = $item['status'];
            $count = (int) ($countsByStatus[$status->value] ?? 0);
            $percent = $totalDocuments > 0 ? round($count / $totalDocuments * 100, 1) : 0;
            $start = $cumulative;
            $cumulative += $percent;
            $item['count'] = $count;
            $item['percent'] = $percent;
            $gradientParts[] = "{$item['color']} {$start}% {$cumulative}%";
        }
        unset($item);
        $gradientString = implode(', ', $gradientParts);

        $popularTypes = DocumentType::withCount('documents')
            ->orderByDesc('documents_count')
            ->take(5)
            ->get();

        $latestDocuments = StudentDocument::with(['student', 'documentType'])
            ->orderByDesc('submitted_at')
            ->take(5)
            ->get();

        $recentActivities = Activity::latest()->take(5)->get();

        return view('admin.tong-quan', [
            'tieuDeTrang' => 'Tổng quan',
            'trangHienTai' => 'tong_quan',
            'totalDocuments' => $totalDocuments,
            'statusChart' => $statusChart,
            'gradientString' => $gradientString,
            'popularTypes' => $popularTypes,
            'latestDocuments' => $latestDocuments,
            'recentActivities' => $recentActivities,
        ]);
    }
}