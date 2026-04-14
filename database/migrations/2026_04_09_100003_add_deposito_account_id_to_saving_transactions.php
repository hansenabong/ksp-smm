<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->foreignId('deposito_account_id')
                ->nullable()
                ->after('saving_product_id')
                ->constrained('deposito_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saving_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deposito_account_id');
        });
    }
};
