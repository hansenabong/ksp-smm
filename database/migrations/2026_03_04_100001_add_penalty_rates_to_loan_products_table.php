<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            // 30 basis points = 0.30%/day late penalty
            $table->unsignedInteger('penalty_daily_rate_bp')->default(30)->after('interest_rate_monthly_bp');
            // 500 basis points = 5% early repayment fee on outstanding principal
            $table->unsignedInteger('early_repayment_rate_bp')->default(500)->after('penalty_daily_rate_bp');
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['penalty_daily_rate_bp', 'early_repayment_rate_bp']);
        });
    }
};
