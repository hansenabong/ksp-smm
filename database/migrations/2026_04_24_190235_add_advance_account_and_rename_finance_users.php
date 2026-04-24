<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add "Advance" liability account for unidentified loan payments
        //    (member paid but didn't include name/PK — finance parks it as Advance).
        $parent2120 = DB::table('accounts')->where('code', '2120')->value('id');

        if (! DB::table('accounts')->where('code', '2124')->exists()) {
            DB::table('accounts')->insert([
                'code'           => '2124',
                'name'           => 'Advance — Angsuran Tidak Teridentifikasi',
                'type'           => 'LIABILITY',
                'normal_balance' => 'C',
                'parent_id'      => $parent2120,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // 2) Add "Advance" INCOME cash category pointing to account 2124
        $advanceAccountId = DB::table('accounts')->where('code', '2124')->value('id');

        if ($advanceAccountId && ! DB::table('cash_categories')->where('name', 'Advance')->where('type', 'INCOME')->exists()) {
            DB::table('cash_categories')->insert([
                'name'        => 'Advance',
                'type'        => 'INCOME',
                'account_id'  => $advanceAccountId,
                'description' => 'Pembayaran angsuran yang belum teridentifikasi nasabahnya.',
                'sort_order'  => 99,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 3) Rename petugas Dilla & Nadia to "Dilla / Nadiah" (team kasir display)
        DB::table('users')->whereIn('email', ['dilla@ksp.local', 'nadia@ksp.local'])
            ->update(['name' => 'Dilla / Nadiah']);
    }

    public function down(): void
    {
        DB::table('cash_categories')
            ->where('name', 'Advance')
            ->where('type', 'INCOME')
            ->delete();

        DB::table('accounts')
            ->where('code', '2124')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('journal_lines')
                  ->whereColumn('journal_lines.account_id', 'accounts.id');
            })
            ->delete();

        DB::table('users')->where('email', 'dilla@ksp.local')->update(['name' => 'Dilla']);
        DB::table('users')->where('email', 'nadia@ksp.local')->update(['name' => 'Nadia']);
    }
};
