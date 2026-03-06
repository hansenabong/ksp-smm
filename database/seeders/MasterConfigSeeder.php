<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Quick setup seeder — runs all master data + configuration WITHOUT demo transactions.
 *
 * Use this for a clean, fully-configured system ready to test immediately:
 *   php artisan db:seed --class=MasterConfigSeeder
 *
 * What it seeds:
 *   1. Roles          — SUPER_ADMIN, ADMIN, MANAGER, BENDAHARA, PETUGAS
 *   2. Users          — 10 test accounts (see UsersSeeder for credentials)
 *   3. Master Data    — 2 cash accounts, 3 saving products, 2 loan products, 15 members
 *   4. COA            — full 5-level Chart of Accounts + ledger auto-linking
 *   5. Cash Categories— income/expense/transfer categories with GL account links
 *
 * For full 6-month demo transactions, run DemoTransactionSeeder afterwards or use:
 *   php artisan db:seed   (runs everything including DemoTransactionSeeder)
 */
class MasterConfigSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            DemoMasterSeeder::class,
            AccountingSeeder::class,
            CashCategorySeeder::class,
        ]);
    }
}
