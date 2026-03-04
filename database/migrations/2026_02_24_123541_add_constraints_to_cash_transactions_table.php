<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // amount harus > 0
        DB::statement("
            ALTER TABLE cash_transactions
            ADD CONSTRAINT cash_transactions_amount_positive
            CHECK (amount > 0)
        ");

        // type hanya boleh 3 nilai
        DB::statement("
            ALTER TABLE cash_transactions
            ADD CONSTRAINT cash_transactions_type_valid
            CHECK (type IN ('INCOME','EXPENSE','TRANSFER'))
        ");

        // aturan kolom wajib tergantung type
        DB::statement("
            ALTER TABLE cash_transactions
            ADD CONSTRAINT cash_transactions_accounts_valid
            CHECK (
                (type = 'INCOME'  AND to_cash_account_id IS NOT NULL AND from_cash_account_id IS NULL)
                OR
                (type = 'EXPENSE' AND from_cash_account_id IS NOT NULL AND to_cash_account_id IS NULL)
                OR
                (type = 'TRANSFER' AND from_cash_account_id IS NOT NULL AND to_cash_account_id IS NOT NULL AND from_cash_account_id <> to_cash_account_id)
            )
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT IF EXISTS cash_transactions_accounts_valid");
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT IF EXISTS cash_transactions_type_valid");
        DB::statement("ALTER TABLE cash_transactions DROP CONSTRAINT IF EXISTS cash_transactions_amount_positive");
    }
};