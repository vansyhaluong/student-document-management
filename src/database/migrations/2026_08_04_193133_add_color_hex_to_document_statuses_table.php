<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_statuses', function (Blueprint $table) {
            $table->string('color_hex', 7)->default('#6b7280')->after('badge_class');
        });
    }

    public function down(): void
    {
        Schema::table('document_statuses', function (Blueprint $table) {
            $table->dropColumn('color_hex');
        });
    }
};
