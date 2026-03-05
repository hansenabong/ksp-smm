<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shu_member_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_period_id')->constrained('shu_periods')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();

            // Metrics used for proportional allocation
            $table->bigInteger('avg_savings')->default(0);       // average saving balance during period
            $table->bigInteger('total_loan_payments')->default(0); // total loan payments during period

            // Allocated SHU portions
            $table->bigInteger('shu_simpanan')->default(0);  // share of jasa_simpanan_amount
            $table->bigInteger('shu_usaha')->default(0);     // share of jasa_usaha_amount
            $table->bigInteger('shu_total')->default(0);     // shu_simpanan + shu_usaha

            $table->unique(['shu_period_id', 'member_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_member_allocations');
    }
};
