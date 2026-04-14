<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_products', function (Blueprint $table) {
            $table->boolean('is_deposito')->default(false)->after('is_withdrawable');
            $table->decimal('default_interest_rate_pct', 5, 2)->nullable()->after('is_deposito');
            $table->unsignedSmallInteger('default_term_months')->nullable()->after('default_interest_rate_pct');
        });
    }

    public function down(): void
    {
        Schema::table('saving_products', function (Blueprint $table) {
            $table->dropColumn(['is_deposito', 'default_interest_rate_pct', 'default_term_months']);
        });
    }
};
