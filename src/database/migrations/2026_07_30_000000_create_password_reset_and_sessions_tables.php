<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thay thế migration mặc định "0001_01_01_000000_create_users_table.php" của Laravel.
 * Bảng `users` thật (username/password_hash/role...) được tạo riêng ở migration
 * 2026_07_30_000003_create_users_table.php để khớp đúng SQL gốc — nên ở đây CHỈ
 * giữ lại password_reset_tokens và sessions (Laravel cần 2 bảng này cho tính năng
 * quên mật khẩu và SESSION_DRIVER=database, không liên quan schema nghiệp vụ).
 *
 * LƯU Ý: phải XÓA file gốc "0001_01_01_000000_create_users_table.php" trước khi
 * thêm file này, nếu không sẽ bị trùng bảng `users`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
