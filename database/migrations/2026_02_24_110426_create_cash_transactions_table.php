<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('code', 40)->unique(); // CK-YYYYMM-000001
            $table->date('trx_date');
            $table->string('type', 10); // INCOME | EXPENSE | TRANSFER

            $table->foreignId('from_cash_account_id')->nullable()->constrained('cash_accounts');
            $table->foreignId('to_cash_account_id')->nullable()->constrained('cash_accounts');

            $table->string('description')->nullable();
            $table->bigInteger('amount'); // rupiah integer

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index(['type', 'trx_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};