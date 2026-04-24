<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $p5100 = DB::table('accounts')->where('code', '5100')->value('id');
        $p5200 = DB::table('accounts')->where('code', '5200')->value('id');
        $p4200 = DB::table('accounts')->where('code', '4200')->value('id');

        $accounts = [
            // --- EXPENSE accounts missing from category dropdown ---
            ['code' => '5117', 'name' => 'Beban Notaris',                    'type' => 'EXPENSE', 'normal_balance' => 'D', 'parent_id' => $p5200],
            ['code' => '5118', 'name' => 'Beban Asuransi',                   'type' => 'EXPENSE', 'normal_balance' => 'D', 'parent_id' => $p5100],
            ['code' => '5119', 'name' => 'Beban Pajak',                      'type' => 'EXPENSE', 'normal_balance' => 'D', 'parent_id' => $p5200],
            ['code' => '5216', 'name' => 'Beban Tolakan Transaksi Keluar',   'type' => 'EXPENSE', 'normal_balance' => 'D', 'parent_id' => $p5200],
            ['code' => '5217', 'name' => 'Beban Admin Transfer / RTGS',      'type' => 'EXPENSE', 'normal_balance' => 'D', 'parent_id' => $p5200],

            // --- REVENUE accounts missing from income category dropdown ---
            ['code' => '4213', 'name' => 'Pendapatan Transfer / RTGS Masuk', 'type' => 'REVENUE', 'normal_balance' => 'C', 'parent_id' => $p4200],
            ['code' => '4214', 'name' => 'Pendapatan Retur / Tolakan Masuk', 'type' => 'REVENUE', 'normal_balance' => 'C', 'parent_id' => $p4200],
        ];

        $now = now();
        foreach ($accounts as $acc) {
            if (DB::table('accounts')->where('code', $acc['code'])->exists()) {
                continue;
            }
            DB::table('accounts')->insert(array_merge($acc, [
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        DB::table('accounts')
            ->whereIn('code', ['5117','5118','5119','5216','5217','4213','4214'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('journal_lines')
                  ->whereColumn('journal_lines.account_id', 'accounts.id');
            })
            ->delete();
    }
};
