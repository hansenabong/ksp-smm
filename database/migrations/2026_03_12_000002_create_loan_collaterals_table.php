<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained('loan_applications')->cascadeOnDelete();
            // Type: SERTIPIKAT, BPKB, DEPOSITO, LAINNYA
            $table->string('type', 30);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('estimated_value')->default(0);
            $table->string('document_path', 500)->nullable();
            $table->string('document_name', 255)->nullable();
            // Status: HELD (in custody) or RELEASED (returned to owner)
            $table->string('status', 20)->default('HELD');
            $table->date('released_at')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('release_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_collaterals');
    }
};
