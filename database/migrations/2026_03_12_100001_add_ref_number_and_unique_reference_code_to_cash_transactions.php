<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->string('ref_number', 150)->nullable()->after('reference_code');
            $table->unique('reference_code', 'cash_transactions_reference_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropUnique('cash_transactions_reference_code_unique');
            $table->dropColumn('ref_number');
        });
    }
};
