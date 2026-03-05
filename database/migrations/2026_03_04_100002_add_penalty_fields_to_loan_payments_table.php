<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->unsignedInteger('penalty_days')->default(0)->after('amount');
            $table->unsignedBigInteger('penalty_amount')->default(0)->after('penalty_days');
            $table->boolean('is_early_repayment')->default(false)->after('penalty_amount');
            $table->unsignedBigInteger('early_repayment_penalty')->default(0)->after('is_early_repayment');
        });
    }

    public function down(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropColumn(['penalty_days', 'penalty_amount', 'is_early_repayment', 'early_repayment_penalty']);
        });
    }
};
