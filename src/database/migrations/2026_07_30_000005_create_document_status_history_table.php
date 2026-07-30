<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_document_id');
            $table->enum('status', [
                'waiting_for_receipt',
                'received',
                'processing',
                'needs_supplement',
                'completed',
                'invalid',
                'cancelled',
            ]);
            $table->string('invalid_reason', 200)->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('changed_by_user_id');
            $table->dateTime('changed_at')->useCurrent();

            $table->index(
                ['student_document_id', 'changed_at'],
                'idx_document_status_history_student_document_id_changed_at'
            );
            $table->index('changed_by_user_id', 'idx_document_status_history_changed_by_user_id');

            $table->foreign('student_document_id', 'fk_document_status_history_student_document_id')
                ->references('id')->on('student_documents')
                ->onUpdate('cascade');

            $table->foreign('changed_by_user_id', 'fk_document_status_history_changed_by_user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade');
        });

        DB::statement("
            ALTER TABLE `document_status_history`
            ADD CONSTRAINT `chk_document_status_history_invalid_reason_matches_status`
            CHECK ((`status` = 'invalid' AND `invalid_reason` IS NOT NULL AND CHAR_LENGTH(TRIM(`invalid_reason`)) BETWEEN 1 AND 200)
                OR (`status` <> 'invalid' AND `invalid_reason` IS NULL))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('document_status_history');
    }
};
