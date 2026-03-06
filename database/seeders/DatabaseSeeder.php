<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seeder order matters:
 *
 *  1. RolesSeeder            — create Spatie roles (required before UsersSeeder)
 *  2. UsersSeeder            — 10 test accounts, one per role (see UsersSeeder for credentials)
 *  3. DemoMasterSeeder       — 2 cash accounts, 3 saving products, 2 loan products, 15 members
 *  4. AccountingSeeder       — full 5-level COA + auto-link ledger accounts to cash/saving/loan
 *  5. CashCategorySeeder     — income/expense/transfer categories with GL account links
 *  6. DemoTransactionSeeder  — 6 months of cash, savings & loan transactions incl. settled/late
 *
 * For master data only (no demo transactions): php artisan db:seed --class=MasterConfigSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
            DemoMasterSeeder::class,
            AccountingSeeder::class,
            CashCategorySeeder::class,
            DemoTransactionSeeder::class,
        ]);
    }
}
