<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('loans', function (Blueprint $table) {
      $table->id();

      $table->foreignId('loan_application_id')->constrained('loan_applications');
      $table->foreignId('member_id')->constrained('members');

      $table->date('disbursed_date'); // tanggal cair
      $table->bigInteger('principal');
      $table->integer('tenor_months');
      $table->decimal('interest_monthly_percent', 6, 2)->default(0);

      // komponen per bulan
      $table->bigInteger('principal_monthly');  // pokok angsuran
      $table->bigInteger('interest_monthly');   // bunga per bulan (flat)
      $table->bigInteger('admin_monthly')->default(0);
      $table->bigInteger('insurance_monthly')->default(0);

      $table->bigInteger('monthly_installment'); // jumlah angsuran per bulan
      $table->bigInteger('total_payable');       // total tagihan
      $table->bigInteger('total_paid')->default(0);
      $table->bigInteger('outstanding');         // sisa tagihan

      $table->string('status', 20)->default('ACTIVE'); // ACTIVE|CLOSED

      // posting ke kas saat pencairan
      $table->foreignId('disbursed_cash_account_id')->constrained('cash_accounts');
      $table->foreignId('disbursed_cash_transaction_id')->constrained('cash_transactions');

      $table->foreignId('created_by')->constrained('users');
      $table->timestamps();

      $table->index(['status', 'disbursed_date']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('loans');
  }
};