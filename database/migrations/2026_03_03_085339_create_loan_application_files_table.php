<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_application_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();

            $table->string('category', 60)->index(); // KTP, KK, BPKB, SERTIFIKAT, DLL
            $table->string('file_path');            // path di storage
            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['loan_application_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_application_files');
    }
};