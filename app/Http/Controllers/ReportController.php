<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\ReportIndexRequest;
use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(ReportIndexRequest $request, ReportService $service): View
    {
        return view('reports.index', [
            ...$service->indexData($request->filters(), $request->user()),
            'filters' => $request->validated(),
        ]);
    }
}
