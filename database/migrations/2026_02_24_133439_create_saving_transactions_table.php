<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('saving_transactions', function (Blueprint $table) {
      $table->id();

      $table->date('trx_date');
      $table->string('type', 10); // DEPOSIT | WITHDRAW
      $table->bigInteger('amount');

      $table->foreignId('member_id')->constrained('members');
      $table->foreignId('saving_product_id')->constrained('saving_products');

      // kas yang dipakai (deposit = kas tujuan, withdraw = kas sumber)
      $table->foreignId('cash_account_id')->constrained('cash_accounts');

      // link ke transaksi kas (buat audit)
      $table->foreignId('cash_transaction_id')->nullable()->constrained('cash_transactions');

      $table->string('description')->nullable();
      $table->foreignId('created_by')->constrained('users');

      $table->timestamps();

      $table->index(['type', 'trx_date']);
      $table->index(['member_id', 'saving_product_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('saving_transactions');
  }
};