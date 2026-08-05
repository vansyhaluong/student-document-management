<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // giá trị lưu trong cột status của đơn, VD: waiting_for_receipt
            $table->string('label', 100); // tên hiển thị, VD: Chờ tiếp nhận
            $table->string('badge_class', 40)->default('badge-mac-dinh'); // class CSS màu badge
            $table->unsignedInteger('sort_order')->default(0); // thứ tự hiển thị trong dropdown
            $table->boolean('is_system')->default(false); // true = 1 trong 7 trạng thái gốc, không cho đổi code/xóa
            $table->boolean('is_active')->default(true); // false = ẩn khỏi dropdown chọn mới, nhưng đơn cũ vẫn giữ nguyên
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_statuses');
    }
};