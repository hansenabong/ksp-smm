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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->string('name', 120)->unique();
            $table->string('method', 20)->default('FLAT'); // FLAT / ANNUITY
            $table->integer('interest_rate_monthly_bp')->default(0); // basis points per month
            $table->bigInteger('admin_fee')->default(0);
            $table->integer('max_tenor_months')->default(12);
            $table->boolean('is_active')->default(true)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['name', 'method', 'interest_rate_monthly_bp', 'admin_fee', 'max_tenor_months', 'is_active']);
        });
    }
};
