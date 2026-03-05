<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure no duplicates exist before adding constraint (safety net)
        \Illuminate\Support\Facades\DB::statement('
            UPDATE cash_accounts ca
            SET ledger_account_id = NULL
            WHERE ledger_account_id IS NOT NULL
              AND (
                SELECT COUNT(*) FROM cash_accounts ca2
                WHERE ca2.ledger_account_id = ca.ledger_account_id
                  AND ca2.id < ca.id
              ) > 0
        ');

        Schema::table('cash_accounts', function (Blueprint $table) {
            // Unique but nullable: in PostgreSQL NULLs are distinct, so multiple NULLs are allowed.
            $table->unique('ledger_account_id', 'cash_accounts_ledger_account_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cash_accounts', function (Blueprint $table) {
            $table->dropUnique('cash_accounts_ledger_account_id_unique');
        });
    }
};
