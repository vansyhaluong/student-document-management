<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->smallIncrements('id');            // smallint(5) unsigned auto_increment
            $table->string('code', 20);
            $table->string('name', 150);
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique('code', 'uq_document_types_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
