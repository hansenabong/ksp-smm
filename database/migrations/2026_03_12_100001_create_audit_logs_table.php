<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);                   // void, approve, reject, login, logout, create, update, delete
            $table->string('subject_type', 100)->nullable(); // e.g. CashTransaction, LoanApplication
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->jsonb('metadata')->nullable();            // extra context (reason, old/new values, etc.)
            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
