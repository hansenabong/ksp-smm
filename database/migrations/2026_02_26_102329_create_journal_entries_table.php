<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('trx_date')->index();

            // untuk mapping balik ke transaksi bisnis
            $table->string('source_type', 80)->nullable()->index(); // cash_transactions / saving_transactions / loans / loan_payments
            $table->unsignedBigInteger('source_id')->nullable()->index();

            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->boolean('is_voided')->default(false)->index();
            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 255)->nullable();

            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
