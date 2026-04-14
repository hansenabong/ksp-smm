<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposito_accounts', function (Blueprint $table) {
            $table->string('bank_account_name', 100)->nullable()->after('maturity_date');
            $table->string('bank_account_no', 50)->nullable()->after('bank_account_name');
            $table->string('bank_name', 100)->nullable()->after('bank_account_no');
        });
    }

    public function down(): void
    {
        Schema::table('deposito_accounts', function (Blueprint $table) {
            $table->dropColumn(['bank_account_name', 'bank_account_no', 'bank_name']);
        });
    }
};
