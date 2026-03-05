<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deactivate pre-seeder legacy COA accounts.
 *
 * Before AccountingSeeder established the proper 5-level hierarchy,
 * accounts were created lazily with ad-hoc codes (1101, 1102, 2101-2104,
 * 1201, 3101, 4101-4105, 5101-5102). AccountingSeeder later created the
 * correct parallel accounts (1111, 1121, 2111-2114, 3111, 4111-4115, etc.)
 * and re-linked all CashAccount / SavingProduct / LoanProduct foreign keys
 * to the new accounts.
 *
 * The legacy accounts can no longer be deleted because journal_lines already
 * reference them (historical data must be preserved). Setting is_active=false
 * removes them from dropdowns and reports without breaking audit history.
 */
return new class extends Migration
{
    /** Codes that belong to the legacy pre-seeder COA */
    private array $legacyCodes = [
        '1101', // Tunai (old — now 1111 under 1110)
        '1102', // BSI   (old — now 1121 under 1120)
        '1201', // Piutang Pinjaman Anggota (old — now 1131+ under 1130)
        '2101', // Simpanan Pokok     (old — now 2111 under 2110)
        '2102', // Simpanan Wajib     (old — now 2112)
        '2103', // Simpanan Sukarela  (old — now 2113)
        '2104', // wajib              (old — now 2114)
        '3101', // Modal/Saldo Awal   (old — now 3111 under 3100)
        '4101', // Pendapatan Bunga Pinjaman    (old — now 4111 under 4100)
        '4102', // Pendapatan Admin Pinjaman     (old — now 4112)
        '4103', // Pendapatan Asuransi           (old — now 4113)
        '4104', // Pendapatan Jasa/Umum          (old — superseded by 4212)
        '4105', // Pendapatan Denda              (old — superseded by 4114/4115)
        '5101', // Beban Operasional             (old — now 5111+ under 5100)
        '5102', // Beban Admin Bank              (old — now 5211 under 5200)
    ];

    public function up(): void
    {
        DB::table('accounts')
            ->whereIn('code', $this->legacyCodes)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        DB::table('accounts')
            ->whereIn('code', $this->legacyCodes)
            ->update(['is_active' => true]);
    }
};
