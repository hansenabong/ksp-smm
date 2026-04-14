<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('gender', 1)->nullable()->after('name');              // L / P
            $table->string('ktp_no', 20)->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('ktp_no');
            $table->date('ktp_expiry')->nullable()->after('birth_date');         // null = seumur hidup
            $table->string('marital_status', 20)->nullable()->after('ktp_expiry'); // BELUM_KAWIN, KAWIN, CERAI_HIDUP, CERAI_MATI
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['gender', 'ktp_no', 'birth_date', 'ktp_expiry', 'marital_status']);
        });
    }
};
