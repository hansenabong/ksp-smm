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
        Schema::table('cash_accounts', function (Blueprint $table) {
            // tambahkan name kalau belum ada
            $table->string('name')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
