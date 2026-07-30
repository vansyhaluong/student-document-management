<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code', 20)->charset('ascii')->collation('ascii_bin');
            $table->string('student_code', 20);
            $table->unsignedSmallInteger('document_type_id');
            $table->enum('status', [
                'waiting_for_receipt',
                'received',
                'processing',
                'needs_supplement',
                'completed',
                'invalid',
                'cancelled',
            ])->default('waiting_for_receipt');
            $table->unsignedBigInteger('assigned_secretary_user_id')->nullable();
            $table->dateTime('submitted_at')->useCurrent();
            $table->dateTime('completed_at')->nullable();
            $table->string('invalid_reason', 200)->nullable();
            $table->string('note', 500)->nullable();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('document_code', 'uq_student_documents_document_code');
            $table->index(['student_code', 'document_type_id'], 'idx_student_documents_student_code_document_type_id');
            $table->index('status', 'idx_student_documents_status');
            $table->index(['document_type_id', 'status'], 'idx_student_documents_document_type_id_status');
            $table->index('submitted_at', 'idx_student_documents_submitted_at');
            $table->index('assigned_secretary_user_id', 'idx_student_documents_assigned_secretary_user_id');

            $table->foreign('student_code', 'fk_student_documents_student_code')
                ->references('student_code')->on('students')
                ->onUpdate('cascade');

            $table->foreign('document_type_id', 'fk_student_documents_document_type_id')
                ->references('id')->on('document_types')
                ->onUpdate('cascade');

            $table->foreign('assigned_secretary_user_id', 'fk_student_documents_assigned_secretary_user_id')
                ->references('id')->on('users')
                ->onUpdate('cascade');
        });

        // CHECK constraints (khớp nguyên văn file SQL gốc)
        DB::statement("
            ALTER TABLE `student_documents`
            ADD CONSTRAINT `chk_student_documents_invalid_reason_matches_status`
            CHECK ((`status` = 'invalid' AND `invalid_reason` IS NOT NULL AND CHAR_LENGTH(TRIM(`invalid_reason`)) BETWEEN 1 AND 200)
                OR (`status` <> 'invalid' AND `invalid_reason` IS NULL))
        ");

        DB::statement("
            ALTER TABLE `student_documents`
            ADD CONSTRAINT `chk_student_documents_completed_at_matches_status`
            CHECK ((`status` = 'completed' AND `completed_at` IS NOT NULL)
                OR (`status` <> 'completed' AND `completed_at` IS NULL))
        ");

        // Trigger: document_code không được sửa sau khi tạo
        DB::unprepared("
            CREATE TRIGGER `trg_student_documents_document_code_immutable`
            BEFORE UPDATE ON `student_documents` FOR EACH ROW
            BEGIN
              IF NOT (NEW.`document_code` <=> OLD.`document_code`) THEN
                SIGNAL SQLSTATE '45000'
                  SET MESSAGE_TEXT = 'document_code cannot be changed after creation';
              END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_student_documents_document_code_immutable`');
        Schema::dropIfExists('student_documents');
    }
};
