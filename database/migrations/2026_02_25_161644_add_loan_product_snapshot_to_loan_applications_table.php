<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_applications', 'loan_product_id')) {
                $table->foreignId('loan_product_id')->nullable()->constrained('loan_products');
            }

            if (!Schema::hasColumn('loan_applications', 'method')) {
                $table->string('method', 20)->nullable(); // snapshot
            }

            if (!Schema::hasColumn('loan_applications', 'interest_rate_monthly_bp')) {
                $table->integer('interest_rate_monthly_bp')->default(0); // snapshot
            }

            if (!Schema::hasColumn('loan_applications', 'admin_fee')) {
                $table->bigInteger('admin_fee')->default(0); // snapshot
            }

        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (Schema::hasColumn('loan_applications', 'loan_product_id')) {
                $table->dropConstrainedForeignId('loan_product_id');
            }
            if (Schema::hasColumn('loan_applications', 'method')) {
                $table->dropColumn('method');
            }
            if (Schema::hasColumn('loan_applications', 'interest_rate_monthly_bp')) {
                $table->dropColumn('interest_rate_monthly_bp');
            }
            if (Schema::hasColumn('loan_applications', 'admin_fee')) {
                $table->dropColumn('admin_fee');
            }
        });
    }
};