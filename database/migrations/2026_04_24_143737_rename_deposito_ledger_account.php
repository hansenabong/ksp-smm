<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_NAME = 'Deposito';
    private const NEW_NAME = 'Deposito Berjangka — Dana Anggota Dikelola';

    public function up(): void
    {
        // Rename the cash-side deposito ledger account so the balance sheet
        // clearly labels it as member-managed funds, not operational assets.
        DB::table('accounts')
            ->where('code', '1111')
            ->where('name', self::OLD_NAME)
            ->update(['name' => self::NEW_NAME]);
    }

    public function down(): void
    {
        DB::table('accounts')
            ->where('code', '1111')
            ->where('name', self::NEW_NAME)
            ->update(['name' => self::OLD_NAME]);
    }
};
