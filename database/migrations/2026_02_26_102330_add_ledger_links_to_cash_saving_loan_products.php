<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->foreignId('ledger_account_id')->nullable()
                ->constrained('accounts')->nullOnDelete();
        });

        Schema::table('saving_products', function (Blueprint $table) {
            $table->foreignId('ledger_account_id')->nullable()
                ->constrained('accounts')->nullOnDelete();
        });

        Schema::table('loan_products', function (Blueprint $table) {
            $table->foreignId('receivable_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('interest_income_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('admin_income_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('insurance_income_account_id')->nullable()->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receivable_account_id');
            $table->dropConstrainedForeignId('interest_income_account_id');
            $table->dropConstrainedForeignId('admin_income_account_id');
            $table->dropConstrainedForeignId('insurance_income_account_id');
        });

        Schema::table('saving_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ledger_account_id');
        });

        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ledger_account_id');
        });
    }
};
