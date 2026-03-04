<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('loan_payments', function (Blueprint $table) {
      $table->id();

      $table->foreignId('loan_id')->constrained('loans');
      $table->date('trx_date');
      $table->bigInteger('amount');

      // kas tujuan pembayaran (uang masuk)
      $table->foreignId('cash_account_id')->constrained('cash_accounts');
      $table->foreignId('cash_transaction_id')->constrained('cash_transactions');

      $table->string('note')->nullable();
      $table->foreignId('created_by')->constrained('users');
      $table->timestamps();

      $table->index(['loan_id', 'trx_date']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('loan_payments');
  }
};