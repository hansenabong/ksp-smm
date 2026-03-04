<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->index(['type']);
            $table->index(['to_cash_account_id']);
            $table->index(['from_cash_account_id']);
            $table->index(['trx_date']);
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['to_cash_account_id']);
            $table->dropIndex(['from_cash_account_id']);
            $table->dropIndex(['trx_date']);
        });
    }
};