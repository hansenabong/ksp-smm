<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();        // contoh: 1101
            $table->string('name', 150);                 // contoh: Kas Tunai
            $table->enum('type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
            $table->enum('normal_balance', ['D', 'C']);   // normal debit/credit
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
