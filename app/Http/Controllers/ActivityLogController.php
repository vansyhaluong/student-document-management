<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLogs\ActivityLogIndexRequest;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(
        ActivityLogIndexRequest $request,
        ActivityLogService $service,
    ): View {
        return view('activity-log.index', [
            ...$service->indexData($request->filters()),
            'filters' => $request->validated(),
        ]);
    }

    public function show(int $activityLog, Request $request, ActivityLogService $service): View
    {
        Gate::forUser($request->user())->authorize('view-activity-log');

        return view('activity-log.show', $service->detail($activityLog));
    }
}
