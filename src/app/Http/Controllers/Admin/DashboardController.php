<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
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

        $statusChart = [
            ['status' => DocumentStatus::WaitingForReceipt, 'color' => '#1e4fd6'],
            ['status' => DocumentStatus::Received,          'color' => '#16a34a'],
            ['status' => DocumentStatus::Processing,        'color' => '#f59e0b'],
            ['status' => DocumentStatus::NeedsSupplement,   'color' => '#eab308'],
            ['status' => DocumentStatus::Completed,         'color' => '#7c3aed'],
            ['status' => DocumentStatus::Invalid,           'color' => '#dc2626'],
            ['status' => DocumentStatus::Cancelled,         'color' => '#6b7280'],
        ];

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
