<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $causerId = $request->query('nguoi_dung_id', '');
        $tuNgay   = $request->query('tu_ngay', '');
        $denNgay  = $request->query('den_ngay', '');

        $query = Activity::query()->with('causer')->latest();

        if ($user->role !== 'admin') {
            // Secretary/Staff chỉ xem được nhật ký do chính mình thực hiện
            $query->where('causer_id', $user->id);
        } elseif ($causerId !== '') {
            // Admin có thể lọc theo 1 cán bộ cụ thể
            $query->where('causer_id', $causerId);
        }

        if ($tuNgay !== '') {
            $query->whereDate('created_at', '>=', $tuNgay);
        }
        if ($denNgay !== '') {
            $query->whereDate('created_at', '<=', $denNgay);
        }

        $activities = $query->paginate(15)->withQueryString();

        $accounts = $user->role === 'admin'
            ? User::orderBy('full_name')->get()
            : collect();

        return view('admin.lich-su-thao-tac', [
            'tieuDeTrang'    => 'Lịch sử thao tác',
            'trangHienTai'   => 'lich_su',
            'activities'     => $activities,
            'accounts'       => $accounts,
            'causerId'       => $causerId,
            'tuNgay'         => $tuNgay,
            'denNgay'        => $denNgay,
        ]);
    }
}