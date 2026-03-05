<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashCategory;
use Illuminate\Database\Seeder;

class CashCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Helper: resolve account_id by COA code (null-safe if COA not seeded yet)
        $acc = fn(string $code) => Account::where('code', $code)->value('id');

        $categories = [
            // ─── INCOME ────────────────────────────────────────────────────────
            // DR Cash / CR counterpart account
            ['type' => 'INCOME', 'sort' => 1,  'name' => 'Modal / Saldo Awal',      'code' => '3111'], // Simpanan Pokok (Modal)
            ['type' => 'INCOME', 'sort' => 2,  'name' => 'Pendapatan Jasa',          'code' => '4212'], // Pendapatan Non-Operasional
            ['type' => 'INCOME', 'sort' => 3,  'name' => 'Denda',                    'code' => '4212'],
            ['type' => 'INCOME', 'sort' => 4,  'name' => 'Pendapatan Lainnya',       'code' => '4212'],
            ['type' => 'INCOME', 'sort' => 99, 'name' => 'Lainnya',                  'code' => '4212'],

            // ─── EXPENSE ───────────────────────────────────────────────────────
            // DR counterpart account / CR Cash
            ['type' => 'EXPENSE', 'sort' => 1,  'name' => 'Operasional',             'code' => '5213'], // Beban Lain-lain
            ['type' => 'EXPENSE', 'sort' => 2,  'name' => 'Biaya Admin / Bank',      'code' => '5211'], // Beban Administrasi Bank
            ['type' => 'EXPENSE', 'sort' => 3,  'name' => 'Pembelian / ATK',         'code' => '5213'],
            ['type' => 'EXPENSE', 'sort' => 4,  'name' => 'Transport',               'code' => '5213'],
            ['type' => 'EXPENSE', 'sort' => 5,  'name' => 'Gaji / Insentif',         'code' => '5111'], // Beban Gaji (if exists)
            ['type' => 'EXPENSE', 'sort' => 99, 'name' => 'Lainnya',                 'code' => '5213'],

            // ─── TRANSFER ──────────────────────────────────────────────────────
            // Both sides are cash ledger accounts — no counterpart account needed
            ['type' => 'TRANSFER', 'sort' => 1,  'name' => 'Setor Bank',             'code' => null],
            ['type' => 'TRANSFER', 'sort' => 2,  'name' => 'Tarik Tunai',            'code' => null],
            ['type' => 'TRANSFER', 'sort' => 3,  'name' => 'Transfer Antar Kas',     'code' => null],
            ['type' => 'TRANSFER', 'sort' => 99, 'name' => 'Lainnya',                'code' => null],
        ];

        foreach ($categories as $cat) {
            CashCategory::firstOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']],
                [
                    'account_id'  => $cat['code'] ? $acc($cat['code']) : null,
                    'sort_order'  => $cat['sort'],
                    'is_active'   => true,
                ]
            );
        }
    }
}
