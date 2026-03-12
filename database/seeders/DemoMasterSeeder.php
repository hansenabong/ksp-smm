<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\SavingProduct;
use Illuminate\Database\Seeder;

/**
 * Creates master data required for demo transactions.
 * Must run BEFORE AccountingSeeder so that COA ledger accounts get
 * auto-linked to the new CashAccounts / SavingProducts / LoanProducts.
 */
class DemoMasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->cashAccounts();
        $this->savingProducts();
        $this->loanProducts();
        $this->members();
    }

    // =========================================================================
    private function cashAccounts(): void
    {
        $accounts = [
            ['name' => 'Kas Tunai', 'type' => 'CASH'],
            ['name' => 'Bank BRI',  'type' => 'BANK'],
        ];

        foreach ($accounts as $a) {
            CashAccount::firstOrCreate(
                ['name' => $a['name']],
                ['type' => $a['type'], 'is_active' => true]
            );
        }
    }

    // =========================================================================
    private function savingProducts(): void
    {
        $products = [
            ['name' => 'Simpanan Wajib',    'is_withdrawable' => false],
            ['name' => 'Simpanan Sukarela', 'is_withdrawable' => true],
            ['name' => 'Simpanan Deposito', 'is_withdrawable' => false],
        ];

        foreach ($products as $p) {
            SavingProduct::firstOrCreate(
                ['name' => $p['name']],
                ['is_active' => true, 'is_withdrawable' => $p['is_withdrawable']]
            );
        }
    }

    // =========================================================================
    private function loanProducts(): void
    {
        $products = [
            [
                'name'                   => 'Pinjaman Reguler',
                'method'                 => 'FLAT',
                'interest_rate_monthly_bp'=> 150,  // 1.5%/bulan
                'admin_fee'              => 50000,
                'max_tenor_months'       => 24,
                'penalty_daily_rate_bp'  => 30,   // 0.3%/hari
                'early_repayment_rate_bp'=> 500,  // 5% pelunasan cepat
            ],
            [
                'name'                   => 'Pinjaman Mikro',
                'method'                 => 'FLAT',
                'interest_rate_monthly_bp'=> 100,  // 1%/bulan
                'admin_fee'              => 25000,
                'max_tenor_months'       => 12,
                'penalty_daily_rate_bp'  => 20,   // 0.2%/hari
                'early_repayment_rate_bp'=> 500,  // 5% pelunasan cepat
            ],
        ];

        foreach ($products as $p) {
            LoanProduct::updateOrCreate(
                ['name' => $p['name']],
                array_merge($p, ['is_active' => true])
            );
        }
    }

    // =========================================================================
    private function members(): void
    {
        $members = [
            ['member_no' => 'KSP-001', 'name' => 'Budi Santoso',       'phone' => '081234567001', 'address' => 'Jl. Merdeka No. 1, Jakarta'],
            ['member_no' => 'KSP-002', 'name' => 'Siti Rahayu',        'phone' => '081234567002', 'address' => 'Jl. Sudirman No. 12, Bandung'],
            ['member_no' => 'KSP-003', 'name' => 'Ahmad Fauzi',        'phone' => '081234567003', 'address' => 'Jl. Gatot Subroto No. 5, Surabaya'],
            ['member_no' => 'KSP-004', 'name' => 'Dewi Lestari',       'phone' => '081234567004', 'address' => 'Jl. Diponegoro No. 8, Yogyakarta'],
            ['member_no' => 'KSP-005', 'name' => 'Hendra Wijaya',      'phone' => '081234567005', 'address' => 'Jl. Ahmad Yani No. 22, Semarang'],
            ['member_no' => 'KSP-006', 'name' => 'Rina Wulandari',     'phone' => '081234567006', 'address' => 'Jl. Pahlawan No. 3, Malang'],
            ['member_no' => 'KSP-007', 'name' => 'Dodi Prasetyo',      'phone' => '081234567007', 'address' => 'Jl. Veteran No. 17, Medan'],
            ['member_no' => 'KSP-008', 'name' => 'Fitri Handayani',    'phone' => '081234567008', 'address' => 'Jl. Imam Bonjol No. 9, Palembang'],
            ['member_no' => 'KSP-009', 'name' => 'Eko Wahyudi',        'phone' => '081234567009', 'address' => 'Jl. Hasanuddin No. 4, Makassar'],
            ['member_no' => 'KSP-010', 'name' => 'Yuli Astuti',        'phone' => '081234567010', 'address' => 'Jl. Rajawali No. 6, Denpasar'],
            ['member_no' => 'KSP-011', 'name' => 'Agus Susanto',       'phone' => '081234567011', 'address' => 'Jl. Teuku Umar No. 11, Aceh'],
            ['member_no' => 'KSP-012', 'name' => 'Maya Sari',          'phone' => '081234567012', 'address' => 'Jl. Kartini No. 20, Solo'],
            ['member_no' => 'KSP-013', 'name' => 'Rizky Firmansyah',   'phone' => '081234567013', 'address' => 'Jl. Mawar No. 15, Bogor'],
            ['member_no' => 'KSP-014', 'name' => 'Lina Marlina',       'phone' => '081234567014', 'address' => 'Jl. Anggrek No. 7, Bekasi'],
            ['member_no' => 'KSP-015', 'name' => 'Wahyu Nugroho',      'phone' => '081234567015', 'address' => 'Jl. Melati No. 2, Tangerang'],
        ];

        foreach ($members as $m) {
            Member::firstOrCreate(
                ['member_no' => $m['member_no']],
                array_merge($m, ['is_active' => true])
            );
        }
    }
}
