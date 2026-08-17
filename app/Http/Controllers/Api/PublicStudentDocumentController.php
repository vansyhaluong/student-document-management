<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListPublicStudentDocumentsRequest;
use App\Services\StudentDocumentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicStudentDocumentController extends Controller
{
    public function index(
        ListPublicStudentDocumentsRequest $request,
        StudentDocumentService $service,
    ): JsonResponse {
        return ApiResponse::success(
            $service->publicDocumentsForApi($request->studentCode()),
            'Lấy dữ liệu thành công',
        );
    }
}
