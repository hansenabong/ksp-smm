<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\SavingTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutoPostingService
{
    public function __construct(
        private PostingService $posting
    ) {}

    private function alreadyPosted(string $sourceType, int $sourceId): bool
    {
        return JournalEntry::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('is_voided', false)
            ->exists();
    }

    private function cashLedgerIdFromCashAccount($cashAccount): int
    {
        $ledgerId = (int)($cashAccount->ledger_account_id ?? 0);
        if ($ledgerId <= 0) {
            throw ValidationException::withMessages([
                'from_cash_account_id' => 'Kas belum punya mapping akun ledger (ledger_account_id). Set dulu di Master Data - Kas.',
            ]);
        }
        return $ledgerId;
    }

    private function requireAccountId(int $id, string $hint): int
    {
        if ($id <= 0) {
            throw ValidationException::withMessages([
                'accounting' => $hint,
            ]);
        }
        return $id;
    }

    private function ensureAccountByCode(string $code, string $name, string $type, string $normal, ?string $parentCode): int
    {
        // normalize normal balance
        $normal = strtoupper($normal);
        if ($normal === 'DEBIT') $normal = 'D';
        if ($normal === 'CREDIT') $normal = 'C';
        if (!in_array($normal, ['D', 'C'], true)) {
            throw ValidationException::withMessages([
                'accounting' => "Normal balance invalid untuk akun $code. Pakai D/C."
            ]);
        }

        $parentId = null;
        if ($parentCode) {
            $parent = Account::where('code', $parentCode)->first();
            if ($parent) $parentId = $parent->id;
        }

        $acc = Account::firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'type' => $type,                  // ASSET/LIABILITY/EQUITY/REVENUE/EXPENSE
                'normal_balance' => $normal,       // wajib
                'parent_id' => $parentId,
                'is_active' => true,
            ]
        );

        return (int)$acc->id;
    }

    /**
     * Manual Kas (INCOME/EXPENSE/TRANSFER) -> Journal
     * IMPORTANT: kita exclude kategori sistem (Setoran Simpanan, Angsuran, dst)
     */
    public function postCashTransaction(CashTransaction $tx): void
    {
        if ($tx->is_voided) return;
        if ($this->alreadyPosted('cash_transactions', $tx->id)) return;

        $systemCats = ['Setoran Simpanan', 'Penarikan Simpanan', 'Pencairan Pinjaman', 'Angsuran Pinjaman'];
        if ($tx->category && in_array($tx->category, $systemCats, true)) {
            return; // diposting dari modul Savings/Loans
        }

        // Pastikan akun offset tersedia (kalau belum ada, kita create by code)
        $accModal   = $this->ensureAccountByCode('3101', 'Modal/Saldo Awal', 'EQUITY',  'CREDIT', '3000');
        $accRevJasa = $this->ensureAccountByCode('4104', 'Pendapatan Jasa/Umum', 'REVENUE', 'CREDIT', '4000');
        $accRevDnd  = $this->ensureAccountByCode('4105', 'Pendapatan Denda', 'REVENUE', 'CREDIT', '4000');

        $accExpOps  = $this->ensureAccountByCode('5101', 'Beban Operasional', 'EXPENSE', 'DEBIT', '5000');
        $accExpAdm  = $this->ensureAccountByCode('5102', 'Beban Admin/Bank',  'EXPENSE', 'DEBIT', '5000');

        $lines = [];

        if ($tx->type === 'INCOME') {
            $cashId = $this->cashLedgerIdFromCashAccount($tx->toCashAccount);

            $offset = match ($tx->category) {
                'Modal/Saldo Awal' => $accModal,
                'Denda'            => $accRevDnd,
                default            => $accRevJasa, // Pendapatan Jasa, Manual, Lainnya
            };

            $lines = [
                ['account_id' => $cashId, 'dc' => 'D', 'amount' => (int)$tx->amount],
                ['account_id' => $offset, 'dc' => 'C', 'amount' => (int)$tx->amount],
            ];
        }

        if ($tx->type === 'EXPENSE') {
            $cashId = $this->cashLedgerIdFromCashAccount($tx->fromCashAccount);
            $offset = match ($tx->category) {
                'Biaya Admin/Bank' => $accExpAdm,
                default            => $accExpOps, // Operasional, Manual, Lainnya
            };

            $lines = [
                ['account_id' => $offset, 'dc' => 'D', 'amount' => (int)$tx->amount], // Beban naik (Debit)
                ['account_id' => $cashId, 'dc' => 'C', 'amount' => (int)$tx->amount], // Kas turun (Credit)
            ];
        }

        if ($tx->type === 'TRANSFER') {
            $fromId = $this->cashLedgerIdFromCashAccount($tx->fromCashAccount);
            $toId   = $this->cashLedgerIdFromCashAccount($tx->toCashAccount);

            $lines = [
                ['account_id' => $toId,   'dc' => 'D', 'amount' => (int)$tx->amount], // kas tujuan naik
                ['account_id' => $fromId, 'dc' => 'C', 'amount' => (int)$tx->amount], // kas sumber turun
            ];
        }
        if (!$lines) return;

        $this->posting->createEntry([
            'trx_date' => $tx->trx_date,
            'source_type' => 'cash_transactions',
            'source_id' => $tx->id,
            'description' => "Kas {$tx->type} ({$tx->category}) - {$tx->code}",
            'created_by' => $tx->created_by,
        ], $lines);
    }

    /**
     * Simpanan (DEPOSIT/WITHDRAW) -> Journal
     */
    public function postSavingTransaction(SavingTransaction $st): void
    {
        if ($st->is_voided) return;
        if ($this->alreadyPosted('saving_transactions', $st->id)) return;

        $cashLedger = $this->cashLedgerIdFromCashAccount($st->cashAccount);

        $savingLedger = $this->requireAccountId(
            (int)($st->savingProduct?->ledger_account_id ?? 0),
            'Jenis Simpanan belum punya mapping akun ledger (ledger_account_id). Set dulu di Master Data - Jenis Simpanan.'
        );

        $amount = (int)$st->amount;

        if ($st->type === 'DEPOSIT') {
            $lines = [
                ['account_id' => $cashLedger,   'dc' => 'D', 'amount' => $amount],
                ['account_id' => $savingLedger, 'dc' => 'C', 'amount' => $amount],
            ];
        } else { // WITHDRAW
            $lines = [
                ['account_id' => $savingLedger, 'dc' => 'D', 'amount' => $amount],
                ['account_id' => $cashLedger,   'dc' => 'C', 'amount' => $amount],
            ];
        }

        $this->posting->createEntry([
            'trx_date'     => $st->trx_date,
            'source_type'  => 'saving_transactions',
            'source_id'    => $st->id,
            'description'  => "Simpanan {$st->type} - {$st->code}",
            'created_by'   => $st->created_by,
        ], $lines);
    }

    /**
     * Pencairan pinjaman (Approve) -> Journal
     * DR Piutang Pinjaman, CR Kas
     */
    public function postLoanDisbursement(Loan $loan): void
    {
        if (($loan->is_voided ?? false)) return;
        if ($this->alreadyPosted('loans', $loan->id)) return;

        $cashLedger = $this->cashLedgerIdFromCashAccount($loan->disbursedCashAccount);

        $recvLedger = $this->requireAccountId(
            (int)($loan->loanProduct?->receivable_account_id ?? 0),
            'Loan Product belum punya mapping akun Piutang (receivable_account_id). Set dulu di Master Data - Jenis Pinjaman.'
        );

        $amount = (int)$loan->principal;

        $lines = [
            ['account_id' => $recvLedger, 'dc' => 'D', 'amount' => $amount],
            ['account_id' => $cashLedger, 'dc' => 'C', 'amount' => $amount],
        ];

        $trxDate = $loan->disbursed_date ?? $loan->created_at?->toDateString() ?? now()->toDateString();

        $this->posting->createEntry([
            'trx_date'    => $trxDate,
            'source_type' => 'loans',
            'source_id'   => $loan->id,
            'description' => "Pencairan Pinjaman - Loan#{$loan->id}",
            'created_by'  => $loan->created_by,
        ], $lines);
    }

    /**
     * Pembayaran angsuran -> Journal
     * DR Kas, CR Piutang (pokok), CR Pendapatan (bunga/admin/asuransi)
     */
    public function postLoanPayment(LoanPayment $p): void
    {
        if ($p->is_voided) return;
        if ($this->alreadyPosted('loan_payments', $p->id)) return;

        $loan = $p->loan;

        $cashLedger = $this->cashLedgerIdFromCashAccount($p->cashAccount);

        $recvLedger = $this->requireAccountId(
            (int)($loan->loanProduct?->receivable_account_id ?? 0),
            'Loan Product belum punya mapping akun Piutang (receivable_account_id).'
        );

        $accBunga = $this->ensureAccountByCode('4101', 'Pendapatan Bunga Pinjaman', 'REVENUE', 'CREDIT', '4000');
        $accAdmin = $this->ensureAccountByCode('4102', 'Pendapatan Admin Pinjaman', 'REVENUE', 'CREDIT', '4000');
        $accAsur  = $this->ensureAccountByCode('4103', 'Pendapatan Asuransi',       'REVENUE', 'CREDIT', '4000');

        $amount = (int)$p->amount;

        $interest = (int) round(((int)$loan->principal) * ((float)$loan->interest_monthly_percent / 100.0));
        $admin    = (int) ($loan->admin_monthly ?? 0);
        $ins      = (int) ($loan->insurance_monthly ?? 0);

        $principalPart = $amount - ($interest + $admin + $ins);

        if ($principalPart < 0) {
            throw ValidationException::withMessages([
                'amount' => 'Komponen angsuran tidak valid (pokok negatif). Cek setting bunga/admin/asuransi.',
            ]);
        }

        $lines = [
            ['account_id' => $cashLedger, 'dc' => 'D', 'amount' => $amount],
        ];

        if ($principalPart > 0) $lines[] = ['account_id' => $recvLedger, 'dc' => 'C', 'amount' => $principalPart];
        if ($interest > 0)      $lines[] = ['account_id' => $accBunga,   'dc' => 'C', 'amount' => $interest];
        if ($admin > 0)         $lines[] = ['account_id' => $accAdmin,   'dc' => 'C', 'amount' => $admin];
        if ($ins > 0)           $lines[] = ['account_id' => $accAsur,    'dc' => 'C', 'amount' => $ins];

        $this->posting->createEntry([
            'trx_date'    => $p->trx_date,
            'source_type' => 'loan_payments',
            'source_id'   => $p->id,
            'description' => "Angsuran Pinjaman - Payment#{$p->id} Loan#{$loan->id}",
            'created_by'  => $p->created_by,
        ], $lines);
    }

    /**
     * VOID Journal by source (dipakai controller VOID)
     */
    public function voidBySource(string $sourceType, int $sourceId, string $reason): void
    {
        $this->posting->voidBySource($sourceType, $sourceId, $reason);
    }
}
