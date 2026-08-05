<?php

use App\Http\Controllers\Admin\DocumentStatusController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\LookupDocumentController;
use App\Http\Controllers\Public\SubmitDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ===== Nộp hồ sơ (quy trình 3 bước) =====
Route::get('/nop-don', [SubmitDocumentController::class, 'showStep1'])->name('nop-don');
Route::post('/nop-don', [SubmitDocumentController::class, 'postStep1'])->name('nop-don.submit');
Route::get('/nop-don/chi-tiet', [SubmitDocumentController::class, 'showStep2'])->name('nop-don.chi-tiet');
Route::post('/nop-don/chi-tiet', [SubmitDocumentController::class, 'postStep2'])->name('nop-don.chi-tiet.submit');
Route::get('/nop-don/thanh-cong', [SubmitDocumentController::class, 'showSuccess'])->name('nop-don.thanh-cong');

// ===== Tra cứu hồ sơ =====
Route::get('/tra-cuu', [LookupDocumentController::class, 'index'])->name('tra-cuu');
Route::get('/tra-cuu/chi-tiet', [LookupDocumentController::class, 'show'])->name('tra-cuu.chi-tiet');

// ===== Khu vực quản trị (Admin/Thư ký/Nhân viên) =====
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tong-quan', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/quan-ly-don', [DocumentController::class, 'index'])->name('quan-ly-don');
    Route::get('/quan-ly-don/{id}', [DocumentController::class, 'show'])->name('quan-ly-don.chi-tiet');
    Route::post('/quan-ly-don/{id}', [DocumentController::class, 'update'])->name('quan-ly-don.cap-nhat');

    Route::get('/lich-su-thao-tac', [ActivityLogController::class, 'index'])->name('lich-su');

    Route::middleware('role:admin')->group(function () {
        Route::get('/quan-ly-tai-khoan', [AccountController::class, 'index'])->name('quan-ly-tai-khoan');
        Route::get('/quan-ly-tai-khoan/them', [AccountController::class, 'create'])->name('quan-ly-tai-khoan.them');
        Route::post('/quan-ly-tai-khoan', [AccountController::class, 'store'])->name('quan-ly-tai-khoan.luu');
        Route::get('/quan-ly-tai-khoan/{id}/sua', [AccountController::class, 'edit'])->name('quan-ly-tai-khoan.sua');
        Route::post('/quan-ly-tai-khoan/{id}', [AccountController::class, 'update'])->name('quan-ly-tai-khoan.cap-nhat');
        Route::post('/quan-ly-tai-khoan/{id}/doi-trang-thai', [AccountController::class, 'toggleStatus'])->name('quan-ly-tai-khoan.doi-trang-thai');

        Route::get('/quan-ly-loai-don', [DocumentTypeController::class, 'index'])->name('quan-ly-loai-don');
        Route::get('/quan-ly-loai-don/them', [DocumentTypeController::class, 'create'])->name('quan-ly-loai-don.them');
        Route::post('/quan-ly-loai-don', [DocumentTypeController::class, 'store'])->name('quan-ly-loai-don.luu');
        Route::get('/quan-ly-loai-don/{id}/sua', [DocumentTypeController::class, 'edit'])->name('quan-ly-loai-don.sua');
        Route::post('/quan-ly-loai-don/{id}', [DocumentTypeController::class, 'update'])->name('quan-ly-loai-don.cap-nhat');
        Route::post('/quan-ly-loai-don/{id}/doi-trang-thai', [DocumentTypeController::class, 'toggleStatus'])->name('quan-ly-loai-don.doi-trang-thai');

        Route::get('/quan-ly-trang-thai', [DocumentStatusController::class, 'index'])->name('quan-ly-trang-thai');
        Route::get('/quan-ly-trang-thai/them', [DocumentStatusController::class, 'create'])->name('quan-ly-trang-thai.them');
        Route::post('/quan-ly-trang-thai', [DocumentStatusController::class, 'store'])->name('quan-ly-trang-thai.luu');
        Route::get('/quan-ly-trang-thai/{id}/sua', [DocumentStatusController::class, 'edit'])->name('quan-ly-trang-thai.sua');
        Route::post('/quan-ly-trang-thai/{id}', [DocumentStatusController::class, 'update'])->name('quan-ly-trang-thai.cap-nhat');
        Route::post('/quan-ly-trang-thai/{id}/doi-trang-thai', [DocumentStatusController::class, 'toggleStatus'])->name('quan-ly-trang-thai.doi-trang-thai');
        Route::post('/quan-ly-trang-thai/{id}/xoa', [DocumentStatusController::class, 'destroy'])->name('quan-ly-trang-thai.xoa');
    });

    Route::middleware('role:admin,secretary')->group(function () {
        Route::get('/bao-cao-thong-ke', [ReportController::class, 'index'])->name('bao-cao');
        Route::get('/bao-cao-thong-ke/xuat', [ReportController::class, 'export'])->name('bao-cao.xuat');
    });
});
