<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow non-member depositors: make member_id nullable on both tables.
        Schema::table('deposito_accounts', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
            $table->string('depositor_name', 100)->nullable()->after('member_id');
            $table->string('depositor_phone', 20)->nullable()->after('depositor_name');
            $table->string('depositor_ktp', 20)->nullable()->after('depositor_phone');
        });

        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('deposito_accounts', function (Blueprint $table) {
            $table->dropColumn(['depositor_name', 'depositor_phone', 'depositor_ktp']);
            $table->foreignId('member_id')->nullable(false)->change();
        });

        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable(false)->change();
        });
    }
};
