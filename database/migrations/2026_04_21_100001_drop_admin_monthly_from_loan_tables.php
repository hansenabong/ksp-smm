<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (Schema::hasColumn('loan_applications', 'admin_monthly')) {
                $table->dropColumn('admin_monthly');
            }
        });

        Schema::table('loans', function (Blueprint $table) {
            if (Schema::hasColumn('loans', 'admin_monthly')) {
                $table->dropColumn('admin_monthly');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'admin_monthly')) {
                $table->bigInteger('admin_monthly')->default(0);
            }
        });

        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'admin_monthly')) {
                $table->bigInteger('admin_monthly')->default(0);
            }
        });
    }
};
