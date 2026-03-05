<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\LoanProduct;
use App\Models\SavingProduct;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // LEVEL 0 — ROOT ACCOUNTS
        // =====================================================================
        $aset = $this->upsert('1000', 'ASET',       'ASSET',     'D', null);
        $liab = $this->upsert('2000', 'KEWAJIBAN',  'LIABILITY', 'C', null);
        $eq   = $this->upsert('3000', 'EKUITAS',    'EQUITY',    'C', null);
        $rev  = $this->upsert('4000', 'PENDAPATAN', 'REVENUE',   'C', null);
        $exp  = $this->upsert('5000', 'BEBAN',      'EXPENSE',   'D', null);

        // =====================================================================
        // LEVEL 1 — GROUPS
        // =====================================================================
        $asetLancar  = $this->upsert('1100', 'Aktiva Lancar',            'ASSET',     'D', $aset->id);
        $asetTetap   = $this->upsert('1200', 'Aktiva Tetap',             'ASSET',     'D', $aset->id);
        $kewJPendek  = $this->upsert('2100', 'Kewajiban Jangka Pendek',  'LIABILITY', 'C', $liab->id);
        $kewJPanjang = $this->upsert('2200', 'Kewajiban Jangka Panjang', 'LIABILITY', 'C', $liab->id);
        $modalKop    = $this->upsert('3100', 'Modal Koperasi',           'EQUITY',    'C', $eq->id);
        $shu         = $this->upsert('3200', 'Sisa Hasil Usaha (SHU)',   'EQUITY',    'C', $eq->id);
        $pendPinj    = $this->upsert('4100', 'Pendapatan Pinjaman',      'REVENUE',   'C', $rev->id);
        $pendLain    = $this->upsert('4200', 'Pendapatan Lain-lain',     'REVENUE',   'C', $rev->id);
        $bebOps      = $this->upsert('5100', 'Beban Operasional',        'EXPENSE',   'D', $exp->id);
        $bebNonOps   = $this->upsert('5200', 'Beban Non-Operasional',    'EXPENSE',   'D', $exp->id);

        // =====================================================================
        // LEVEL 2 — SUB-GROUPS
        // =====================================================================
        $grpKas      = $this->upsert('1110', 'Kas',               'ASSET',     'D', $asetLancar->id);
        $grpBank     = $this->upsert('1120', 'Rekening Bank',     'ASSET',     'D', $asetLancar->id);
        $grpPiutang  = $this->upsert('1130', 'Piutang Pinjaman',  'ASSET',     'D', $asetLancar->id);
        $grpPiutLain = $this->upsert('1140', 'Piutang Lain-lain', 'ASSET',     'D', $asetLancar->id);
        $grpSimpan   = $this->upsert('2110', 'Simpanan Anggota',  'LIABILITY', 'C', $kewJPendek->id);
        $grpKewLain  = $this->upsert('2120', 'Kewajiban Lain-lain','LIABILITY','C', $kewJPendek->id);

        // =====================================================================
        // FIXED LEAF ACCOUNTS — AKTIVA
        // =====================================================================
        $this->upsert('1141', 'Piutang Bunga Masih Harus Terima',    'ASSET',     'D', $grpPiutLain->id);
        $this->upsert('1142', 'Piutang Denda Masih Harus Terima',    'ASSET',     'D', $grpPiutLain->id);
        $this->upsert('1150', 'Biaya Dibayar Dimuka',                'ASSET',     'D', $asetLancar->id);
        $this->upsert('1211', 'Peralatan Kantor',                    'ASSET',     'D', $asetTetap->id);
        $this->upsert('1212', 'Inventaris / Kendaraan',              'ASSET',     'D', $asetTetap->id);
        $this->upsert('1213', 'Akumulasi Penyusutan',                'ASSET',     'C', $asetTetap->id); // contra-asset

        // =====================================================================
        // FIXED LEAF ACCOUNTS — KEWAJIBAN
        // =====================================================================
        $this->upsert('2121', 'Hutang Pihak Ketiga',                 'LIABILITY', 'C', $grpKewLain->id);
        $this->upsert('2122', 'Pajak Penghasilan Ditanggung',        'LIABILITY', 'C', $grpKewLain->id);
        $this->upsert('2130', 'Dana SHU Belum Dibagi',               'LIABILITY', 'C', $kewJPendek->id);
        $this->upsert('2211', 'Pinjaman Bank / Lembaga Lain',        'LIABILITY', 'C', $kewJPanjang->id);

        // =====================================================================
        // FIXED LEAF ACCOUNTS — EKUITAS
        // =====================================================================
        $this->upsert('3111', 'Simpanan Pokok Anggota (Modal)',      'EQUITY',    'C', $modalKop->id);
        $this->upsert('3112', 'Dana Cadangan Umum',                  'EQUITY',    'C', $modalKop->id);
        $this->upsert('3113', 'Dana Hibah / Donasi',                 'EQUITY',    'C', $modalKop->id);
        $this->upsert('3211', 'SHU Tahun Lalu',                     'EQUITY',    'C', $shu->id);
        $this->upsert('3212', 'SHU Tahun Berjalan',                 'EQUITY',    'C', $shu->id);

        // =====================================================================
        // FIXED LEAF ACCOUNTS — PENDAPATAN
        // =====================================================================
        $this->upsert('4111', 'Pendapatan Bunga Pinjaman',           'REVENUE',   'C', $pendPinj->id);
        $this->upsert('4112', 'Pendapatan Biaya Admin Pinjaman',     'REVENUE',   'C', $pendPinj->id);
        $this->upsert('4113', 'Pendapatan Biaya Provisi / Asuransi', 'REVENUE',   'C', $pendPinj->id);
        $this->upsert('4114', 'Pendapatan Denda Keterlambatan',      'REVENUE',   'C', $pendPinj->id);
        $this->upsert('4115', 'Pendapatan Denda Pelunasan Cepat',    'REVENUE',   'C', $pendPinj->id);
        $this->upsert('4211', 'Pendapatan Jasa Giro / Bunga Bank',   'REVENUE',   'C', $pendLain->id);
        $this->upsert('4212', 'Pendapatan Non-Operasional',          'REVENUE',   'C', $pendLain->id);

        // =====================================================================
        // FIXED LEAF ACCOUNTS — BEBAN
        // =====================================================================
        $this->upsert('5111', 'Beban Gaji & Honorarium',             'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5112', 'Beban Tunjangan & Insentif',          'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5113', 'Beban Sewa Gedung / Kantor',          'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5114', 'Beban Listrik, Air & Telepon',        'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5115', 'Beban ATK & Perlengkapan Kantor',     'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5116', 'Beban Penyusutan Aktiva Tetap',       'EXPENSE',   'D', $bebOps->id);
        $this->upsert('5211', 'Beban Administrasi Bank',             'EXPENSE',   'D', $bebNonOps->id);
        $this->upsert('5212', 'Beban Dana Sosial / Pendidikan',      'EXPENSE',   'D', $bebNonOps->id);
        $this->upsert('5213', 'Beban Lain-lain',                     'EXPENSE',   'D', $bebNonOps->id);

        // =====================================================================
        // DYNAMIC: CashAccount → leaf under 1110 (CASH) or 1120 (BANK)
        // Codes: CASH type → 1111, 1112, ...  BANK type → 1121, 1122, ...
        // =====================================================================
        $cashIdx = ['CASH' => 1, 'BANK' => 1];

        foreach (CashAccount::orderBy('id')->get() as $ca) {
            $isCash = ($ca->type !== 'BANK');
            $parent = $isCash ? $grpKas : $grpBank;
            $prefix = $isCash ? '111' : '112';
            $key    = $isCash ? 'CASH' : 'BANK';
            $code   = $prefix . $cashIdx[$key]++;

            $acc = $this->upsert($code, $ca->name, 'ASSET', 'D', $parent->id);
            $ca->ledger_account_id = $acc->id;
            $ca->save();
        }

        // =====================================================================
        // DYNAMIC: SavingProduct → liability leaf under 2110 (2111, 2112, ...)
        // =====================================================================
        $spIdx = 1;

        foreach (SavingProduct::orderBy('id')->get() as $sp) {
            $code = '211' . $spIdx++;
            $acc  = $this->upsert($code, $sp->name, 'LIABILITY', 'C', $grpSimpan->id);
            $sp->ledger_account_id = $acc->id;
            $sp->save();
        }

        // =====================================================================
        // DYNAMIC: LoanProduct → asset leaf under 1130 (1131, 1132, ...)
        // Income accounts are shared globally (4111 / 4112 / 4113).
        // =====================================================================
        $accBunga = Account::where('code', '4111')->value('id');
        $accAdmin = Account::where('code', '4112')->value('id');
        $accAsur  = Account::where('code', '4113')->value('id');
        $lpIdx    = 1;

        foreach (LoanProduct::orderBy('id')->get() as $lp) {
            $code = '113' . $lpIdx++;
            $recv = $this->upsert($code, 'Piutang ' . $lp->name, 'ASSET', 'D', $grpPiutang->id);
            $lp->receivable_account_id       = $recv->id;
            $lp->interest_income_account_id  = $accBunga;
            $lp->admin_income_account_id     = $accAdmin;
            $lp->insurance_income_account_id = $accAsur;
            $lp->save();
        }
    }

    /**
     * Create account if code doesn't exist; update name + parent if it does.
     * Idempotent — safe to run multiple times.
     */
    private function upsert(string $code, string $name, string $type, string $normal, ?int $parentId): Account
    {
        $acc = Account::firstOrCreate(
            ['code' => $code],
            [
                'name'           => $name,
                'type'           => $type,
                'normal_balance' => $normal,
                'parent_id'      => $parentId,
                'is_active'      => true,
            ]
        );

        if (!$acc->wasRecentlyCreated) {
            $acc->update(['name' => $name, 'parent_id' => $parentId]);
            $acc = $acc->fresh();
        }

        return $acc;
    }
}
