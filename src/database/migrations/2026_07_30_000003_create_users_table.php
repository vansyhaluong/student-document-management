<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100);
            $table->string('password_hash');
            $table->string('full_name', 150);
            $table->string('email')->nullable();
            $table->enum('role', ['staff', 'secretary', 'admin']); // staff = Nhân viên, secretary = Thư ký
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('username', 'uq_users_username');
            $table->unique('email', 'uq_users_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
