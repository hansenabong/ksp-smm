<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();

            $table->enum('dc', ['D', 'C']);         // Debit / Credit
            $table->bigInteger('amount');          // rupiah integer (no float)
            $table->string('memo', 255)->nullable();

            $table->timestamps();

            $table->index(['account_id', 'dc']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
