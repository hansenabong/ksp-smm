<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\SavingProduct;
use App\Models\LoanProduct;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        // Parent utama
        $aset = $this->firstOrCreateAccount('1000', 'ASET', 'ASSET', 'D', null);
        $liab = $this->firstOrCreateAccount('2000', 'LIABILITAS', 'LIABILITY', 'C', null);
        $eq   = $this->firstOrCreateAccount('3000', 'EKUITAS', 'EQUITY', 'C', null);
        $rev  = $this->firstOrCreateAccount('4000', 'PENDAPATAN', 'REVENUE', 'C', null);
        $exp  = $this->firstOrCreateAccount('5000', 'BEBAN', 'EXPENSE', 'D', null);

        // Sub akun penting
        $kasBank = $this->firstOrCreateAccount('1100', 'Kas & Bank', 'ASSET', 'D', $aset->id);
        $piutang = $this->firstOrCreateAccount('1201', 'Piutang Pinjaman Anggota', 'ASSET', 'D', $aset->id);

        $simpAll = $this->firstOrCreateAccount('2100', 'Simpanan Anggota', 'LIABILITY', 'C', $liab->id);

        $bunga   = $this->firstOrCreateAccount('4101', 'Pendapatan Bunga Pinjaman', 'REVENUE', 'C', $rev->id);
        $admin   = $this->firstOrCreateAccount('4102', 'Pendapatan Admin Pinjaman', 'REVENUE', 'C', $rev->id);
        $ins     = $this->firstOrCreateAccount('4103', 'Pendapatan Asuransi', 'REVENUE', 'C', $rev->id);

        $bebOp   = $this->firstOrCreateAccount('5101', 'Beban Operasional', 'EXPENSE', 'D', $exp->id);
        $bebBank = $this->firstOrCreateAccount('5102', 'Beban Admin Bank', 'EXPENSE', 'D', $exp->id);

        // ===== Mapping Kas -> akun 11xx (otomatis)
        $cashAccounts = CashAccount::orderBy('id')->get();
        $i = 1;
        foreach ($cashAccounts as $ca) {
            $code = '11' . str_pad((string)$i, 2, '0', STR_PAD_LEFT); // 1101, 1102, ...
            $acc = $this->firstOrCreateAccount($code, $ca->name, 'ASSET', 'D', $kasBank->id);

            if (!$ca->ledger_account_id) {
                $ca->ledger_account_id = $acc->id;
                $ca->save();
            }
            $i++;
        }

        // ===== Mapping Jenis Simpanan -> akun 21xx (otomatis)
        $savingProducts = SavingProduct::orderBy('id')->get();
        $j = 1;
        foreach ($savingProducts as $sp) {
            $code = '21' . str_pad((string)$j, 2, '0', STR_PAD_LEFT); // 2101, 2102, ...
            $acc = $this->firstOrCreateAccount($code, $sp->name, 'LIABILITY', 'C', $simpAll->id);

            if (!$sp->ledger_account_id) {
                $sp->ledger_account_id = $acc->id;
                $sp->save();
            }
            $j++;
        }

        // ===== Mapping LoanProduct -> pakai akun generic (biar simpel dulu)
        foreach (LoanProduct::all() as $lp) {
            $lp->receivable_account_id = $lp->receivable_account_id ?? $piutang->id;
            $lp->interest_income_account_id = $lp->interest_income_account_id ?? $bunga->id;
            $lp->admin_income_account_id = $lp->admin_income_account_id ?? $admin->id;
            $lp->insurance_income_account_id = $lp->insurance_income_account_id ?? $ins->id;
            $lp->save();
        }

        // Catatan:
        // expense category mapping nanti kita pakai akun beban (5101 / 5102) via posting service
    }

    private function firstOrCreateAccount(string $code, string $name, string $type, string $normal, ?int $parentId): Account
    {
        return Account::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'parent_id' => $parentId, 'is_active' => true]
        );
    }
}
