<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production seeder — seeds ONLY system-level configuration data.
 *
 * No demo users, no demo members, no demo transactions.
 * After running this, create the first Super Admin via:
 *   php artisan ksp:create-superadmin
 *
 * Usage:
 *   php artisan migrate:fresh --seed --seeder=ProductionSeeder
 *
 * What it seeds:
 *   1. Roles               — 7 system roles (SUPER_ADMIN, FINANCE, etc.)
 *   2. Accounting (COA)     — full 5-level Chart of Accounts (fixed accounts only)
 *   3. Cash Categories      — income/expense/transfer categories with GL links
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AccountingSeeder::class,
            CashCategorySeeder::class,
        ]);
    }
}
