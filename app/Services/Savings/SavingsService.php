<?php

namespace App\Services\Savings;

use App\Models\DepositoAccount;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;
use App\Services\Cash\CashTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Accounting\AutoPostingService;

class SavingsService
{
    public function __construct(
        private CashTransactionService $cashService,
        private AutoPostingService $autoPosting
    ) {}

    public function deposit(array $data, int $userId): SavingTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = SavingProduct::lockForUpdate()->findOrFail($data['saving_product_id']);

            // Deposito products: every deposit opens a new fixed-term contract.
            $depositoAccount = null;
            if ($product->is_deposito) {
                $depositoAccount = $this->openDepositoAccount($product, $data, $userId);
            }

            // 1) posting ke kas (INCOME) -> kategori sistem, cash autoposting akan skip
            $cashTx = $this->cashService->createIncome([
                'trx_date' => $data['trx_date'],
                'to_cash_account_id' => $data['cash_account_id'],
                'amount' => $data['amount'],
                // Keep canonical name so AutoPostingService skips the cash-side journal
                // (the savings-side autoposting handles deposito identically to regular savings).
                'category' => 'Setoran Simpanan',
                'description' => $data['description'] ?? null,
            ], $userId);

            // 2) catat di buku simpanan anggota
            $st = SavingTransaction::create([
                'reference_code' => $data['reference_code'] ?? null,
                'trx_date' => $data['trx_date'],
                'type' => 'DEPOSIT',
                'amount' => $data['amount'],
                'member_id' => $data['member_id'],
                'saving_product_id' => $data['saving_product_id'],
                'deposito_account_id' => $depositoAccount?->id,
                'cash_account_id' => $data['cash_account_id'],
                'cash_transaction_id' => $cashTx->id,
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            // autoposting jurnal simpanan (wajib load relasi untuk ledger_account_id)
            $st->load(['cashAccount', 'savingProduct']);
            $this->autoPosting->postSavingTransaction($st);

            return $st;
        });
    }

    public function withdraw(array $data, int $userId): SavingTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $product = SavingProduct::findOrFail($data['saving_product_id']);

            // Deposito withdrawal must reference a specific contract and respect maturity.
            $depositoAccount = null;
            if ($product->is_deposito) {
                $depositoAccount = $this->resolveDepositoForWithdraw($product, $data);
            }

            // cek saldo simpanan cukup
            $bal = $this->getMemberSavingBalance($data['member_id'], $data['saving_product_id']);
            if ($bal < $data['amount']) {
                throw ValidationException::withMessages([
                    'amount' => "Saldo simpanan tidak cukup. Saldo saat ini: Rp " . number_format($bal, 0, ',', '.')
                ]);
            }

            // posting ke kas (EXPENSE) -> kategori sistem, cash autoposting akan skip
            $cashTx = $this->cashService->createExpense([
                'trx_date' => $data['trx_date'],
                'from_cash_account_id' => $data['cash_account_id'],
                'amount' => $data['amount'],
                'category' => 'Penarikan Simpanan',
                'description' => $data['description'] ?? null,
            ], $userId);

            // catat di buku simpanan
            $st = SavingTransaction::create([
                'reference_code' => $data['reference_code'] ?? null,
                'trx_date' => $data['trx_date'],
                'type' => 'WITHDRAW',
                'amount' => $data['amount'],
                'member_id' => $data['member_id'],
                'saving_product_id' => $data['saving_product_id'],
                'deposito_account_id' => $depositoAccount?->id,
                'cash_account_id' => $data['cash_account_id'],
                'cash_transaction_id' => $cashTx->id,
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            // Mark the deposito contract as withdrawn once the principal is fully paid out.
            if ($depositoAccount) {
                $remaining = $depositoAccount->principal_amount
                    - SavingTransaction::notVoided()
                        ->where('deposito_account_id', $depositoAccount->id)
                        ->where('type', 'WITHDRAW')
                        ->sum('amount');

                if ($remaining <= 0) {
                    $depositoAccount->update(['status' => DepositoAccount::STATUS_WITHDRAWN]);
                }
            }

            // autoposting jurnal simpanan
            $st->load(['cashAccount', 'savingProduct']);
            $this->autoPosting->postSavingTransaction($st);

            return $st;
        });
    }

    /**
     * Cair (mencairkan) a matured deposito contract: pay back principal +
     * accrued interest in one operation. Posts TWO records:
     *   1) Principal as a WITHDRAW saving_transaction (Dr Liability / Cr Cash)
     *   2) Interest as an EXPENSE cash_transaction (Dr Beban Bunga Deposito / Cr Cash)
     *
     * Splitting is required so the savings liability ledger doesn't go negative.
     *
     * @return array{principal_tx: SavingTransaction, interest_tx: ?\App\Models\CashTransaction}
     */
    public function cair(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var DepositoAccount $account */
            $account = DepositoAccount::lockForUpdate()->findOrFail($data['deposito_account_id']);

            if ($account->status !== DepositoAccount::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'deposito_account_id' => "Deposito {$account->reference_code} sudah berstatus {$account->status}, tidak dapat dicairkan.",
                ]);
            }

            $trxDate = Carbon::parse($data['trx_date'])->startOfDay();
            if ($trxDate->lessThan($account->maturity_date->startOfDay())) {
                throw ValidationException::withMessages([
                    'trx_date' => sprintf(
                        'Deposito %s belum jatuh tempo. Dapat dicairkan mulai %s.',
                        $account->reference_code,
                        $account->maturity_date->format('d/m/Y'),
                    ),
                ]);
            }

            $principal = (int) $account->principal_amount;
            $interest  = $account->interestAmount();
            $product   = $account->savingProduct;

            // 1) Principal payout via savings withdraw flow
            $cashTxPrincipal = $this->cashService->createExpense([
                'trx_date' => $data['trx_date'],
                'from_cash_account_id' => $data['cash_account_id'],
                'amount' => $principal,
                'category' => 'Penarikan Simpanan',
                'description' => "Cair pokok deposito {$account->reference_code}",
            ], $userId);

            $principalTx = SavingTransaction::create([
                'reference_code' => null,
                'trx_date' => $data['trx_date'],
                'type' => 'WITHDRAW',
                'amount' => $principal,
                'member_id' => $account->member_id,
                'saving_product_id' => $account->saving_product_id,
                'deposito_account_id' => $account->id,
                'cash_account_id' => $data['cash_account_id'],
                'cash_transaction_id' => $cashTxPrincipal->id,
                'description' => "Cair pokok deposito {$account->reference_code}",
                'created_by' => $userId,
            ]);
            $principalTx->load(['cashAccount', 'savingProduct']);
            $this->autoPosting->postSavingTransaction($principalTx);

            // 2) Interest payout via standalone cash EXPENSE (auto-posted as expense
            //    against whatever account the chosen cash_category maps to).
            $interestTx = null;
            if ($interest > 0) {
                if (empty($data['interest_cash_category_id'])) {
                    throw ValidationException::withMessages([
                        'interest_cash_category_id' => 'Kategori kas untuk beban bunga deposito wajib dipilih.',
                    ]);
                }

                $interestTx = $this->cashService->createExpense([
                    'trx_date' => $data['trx_date'],
                    'from_cash_account_id' => $data['cash_account_id'],
                    'amount' => $interest,
                    'cash_category_id' => $data['interest_cash_category_id'],
                    'category' => 'Bunga Deposito',
                    'description' => "Bunga deposito {$account->reference_code} ({$product->name}) - "
                        . $account->interest_rate_pct . '% / ' . $account->term_months . ' bln',
                ], $userId);
            }

            $account->update(['status' => DepositoAccount::STATUS_WITHDRAWN]);

            return [
                'principal_tx' => $principalTx,
                'interest_tx'  => $interestTx,
                'account'      => $account->fresh(),
            ];
        });
    }

    /**
     * Open a new deposito contract for the member. Term + interest are locked
     * from the product's defaults at the moment of opening so future product
     * edits don't retroactively change existing contracts.
     */
    private function openDepositoAccount(SavingProduct $product, array $data, int $userId): DepositoAccount
    {
        if (! $product->default_term_months || $product->default_interest_rate_pct === null) {
            throw ValidationException::withMessages([
                'saving_product_id' => 'Produk deposito belum lengkap: bunga & jangka waktu wajib diisi di master jenis simpanan.',
            ]);
        }

        $startDate    = Carbon::parse($data['trx_date']);
        $maturityDate = $startDate->copy()->addMonths((int) $product->default_term_months);

        return DepositoAccount::create([
            'reference_code'    => $this->generateDepositoReference($startDate),
            'member_id'         => $data['member_id'] ?? null,
            'depositor_name'    => $data['depositor_name'] ?? null,
            'depositor_phone'   => $data['depositor_phone'] ?? null,
            'depositor_ktp'     => $data['depositor_ktp'] ?? null,
            'saving_product_id' => $product->id,
            'principal_amount'  => $data['amount'],
            'interest_rate_pct' => $product->default_interest_rate_pct,
            'term_months'       => $product->default_term_months,
            'start_date'        => $startDate->toDateString(),
            'maturity_date'     => $maturityDate->toDateString(),
            'status'            => DepositoAccount::STATUS_ACTIVE,
            'bank_account_name' => $data['bank_account_name'] ?? null,
            'bank_account_no'   => $data['bank_account_no'] ?? null,
            'bank_name'         => $data['bank_name'] ?? null,
            'created_by'        => $userId,
        ]);
    }

    /**
     * Locate the deposito contract that this withdraw applies to and
     * verify the maturity date has been reached.
     */
    private function resolveDepositoForWithdraw(SavingProduct $product, array $data): DepositoAccount
    {
        $query = DepositoAccount::query()
            ->where('member_id', $data['member_id'])
            ->where('saving_product_id', $product->id)
            ->where('status', DepositoAccount::STATUS_ACTIVE);

        if (! empty($data['deposito_account_id'])) {
            $query->where('id', $data['deposito_account_id']);
        }

        // Pick the oldest still-active contract by default (FIFO).
        $account = $query->orderBy('start_date')->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'saving_product_id' => 'Tidak ada kontrak deposito aktif untuk anggota ini pada produk tersebut.',
            ]);
        }

        $trxDate = Carbon::parse($data['trx_date'])->startOfDay();
        if ($trxDate->lessThan($account->maturity_date->startOfDay())) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Deposito %s belum jatuh tempo. Dapat dicairkan mulai %s.',
                    $account->reference_code,
                    $account->maturity_date->format('d/m/Y'),
                ),
            ]);
        }

        return $account;
    }

    private function generateDepositoReference(Carbon $startDate): string
    {
        $prefix = 'DA-' . $startDate->format('Ym') . '-';
        $last = DepositoAccount::where('reference_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference_code');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function getMemberSavingBalance(int $memberId, int $savingProductId): int
    {
        $deposit = SavingTransaction::notVoided()
            ->where('member_id', $memberId)
            ->where('saving_product_id', $savingProductId)
            ->where('type', 'DEPOSIT')
            ->sum('amount');

        $withdraw = SavingTransaction::notVoided()
            ->where('member_id', $memberId)
            ->where('saving_product_id', $savingProductId)
            ->where('type', 'WITHDRAW')
            ->sum('amount');

        return (int)$deposit - (int)$withdraw;
    }
    public function voidTransaction(int $savingTransactionId, string $reason, int $userId): void
    {
        DB::transaction(function () use ($savingTransactionId, $reason, $userId) {
            $tx = SavingTransaction::lockForUpdate()->findOrFail($savingTransactionId);

            if ($tx->is_voided) {
                throw ValidationException::withMessages(['void' => 'Transaksi sudah di-void.']);
            }

            // void saving transaction
            $tx->forceFill([
                'is_voided' => true,
                'void_reason' => $reason,
                'voided_by' => $userId,
                'voided_at' => now(),
            ])->save();

            // void linked cash transaction (kalau ada)
            if ($tx->cash_transaction_id) {
                $cashTx = \App\Models\CashTransaction::lockForUpdate()->find($tx->cash_transaction_id);
                if ($cashTx && !$cashTx->is_voided) {
                    $cashTx->forceFill([
                        'is_voided' => true,
                        'void_reason' => "VOID Simpanan #{$tx->id}: " . $reason,
                        'voided_by' => $userId,
                        'voided_at' => now(),
                    ])->save();
                }
            }

            // void journal entries for both the saving tx and linked cash tx
            $this->autoPosting->voidBySource('saving_transactions', $tx->id, $reason);
            if ($tx->cash_transaction_id) {
                $this->autoPosting->voidBySource('cash_transactions', $tx->cash_transaction_id, $reason);
            }
        });
    }
}
