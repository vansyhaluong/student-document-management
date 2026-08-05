<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        DB::statement('ALTER TABLE student_documents MODIFY status VARCHAR(30) NOT NULL');
        DB::statement('ALTER TABLE document_status_history MODIFY status VARCHAR(30) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE student_documents MODIFY status ENUM('waiting_for_receipt','received','processing','needs_supplement','completed','invalid','cancelled') NOT NULL");
        DB::statement("ALTER TABLE document_status_history MODIFY status ENUM('waiting_for_receipt','received','processing','needs_supplement','completed','invalid','cancelled') NOT NULL");
    }
};
