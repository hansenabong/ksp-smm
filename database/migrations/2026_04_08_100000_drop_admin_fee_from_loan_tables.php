<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            if (Schema::hasColumn('loan_products', 'admin_fee')) {
                $table->dropColumn('admin_fee');
            }
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            if (Schema::hasColumn('loan_applications', 'admin_fee')) {
                $table->dropColumn('admin_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_products', 'admin_fee')) {
                $table->bigInteger('admin_fee')->default(0);
            }
        });

        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'admin_fee')) {
                $table->bigInteger('admin_fee')->default(0);
            }
        });
    }
};
