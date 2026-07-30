<?php

use Illuminate\Support\Facades\Facade;

return [

    'name' => env('APP_NAME', 'DocTrack'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Hệ thống Tra cứu & Nộp đơn Sinh viên phục vụ SV trong nước, các mốc thời
    | gian ghi trong đơn (submitted_at, processed_at - HD2_KienTrucUngDung mục
    | 4) phải theo giờ Việt Nam để khớp với kỳ vọng của Nhân viên/Thư ký/Admin
    | khi xem lịch sử xử lý. Mặc định Laravel là 'UTC' — đổi sang Asia/Ho_Chi_Minh
    | (đọc được từ .env qua APP_TIMEZONE để dễ đổi giữa môi trường dev/test).
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | Giao diện Web SV/Web quản trị dùng tiếng Việt (nhãn trạng thái "Đã gửi",
    | "Đã tiếp nhận"... theo HD2_KienTrucUngDung mục 3.6). Đặt locale mặc định
    | là 'vi' để các thông báo lỗi validate (Form Request) và lang string dùng
    | tiếng Việt; fallback 'en' phòng khi thiếu bản dịch cho key nào đó.
    |
    */

    'locale' => env('APP_LOCALE', 'vi'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'vi_VN'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
