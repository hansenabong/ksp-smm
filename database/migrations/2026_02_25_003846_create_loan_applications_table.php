<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('loan_applications', function (Blueprint $table) {
      $table->id();
      $table->date('trx_date');

      $table->foreignId('member_id')->constrained('members');
      $table->bigInteger('principal');        // nominal pinjaman
      $table->integer('tenor_months');        // lama angsuran
      $table->decimal('interest_monthly_percent', 6, 2)->default(0); // bunga flat per bulan (%)

      $table->bigInteger('admin_monthly')->default(0);     // biaya admin per bulan (opsional)
      $table->bigInteger('insurance_monthly')->default(0); // asuransi per bulan (opsional)

      $table->string('status', 20)->default('PENDING'); // PENDING|APPROVED|REJECTED
      $table->string('notes')->nullable();              // catatan pengajuan
      $table->string('approval_note')->nullable();      // alasan approve/reject

      $table->foreignId('approved_by')->nullable()->constrained('users');
      $table->timestamp('approved_at')->nullable();
      $table->foreignId('approved_cash_account_id')->nullable()->constrained('cash_accounts'); // ambil dari kas saat cair

      $table->foreignId('created_by')->constrained('users');
      $table->timestamps();

      $table->index(['status', 'trx_date']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('loan_applications');
  }
};