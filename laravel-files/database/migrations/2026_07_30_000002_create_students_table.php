<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('student_code', 20)->primary(); // Khóa chính là MSSV, không có cột id riêng
            $table->string('last_name', 100);
            $table->string('first_name', 50);
            $table->date('date_of_birth')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('email')->nullable();

            $table->unique('email', 'uq_students_email');
            $table->index(['last_name', 'first_name'], 'idx_students_last_name_first_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
