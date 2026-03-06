<?php

namespace App\Services\Savings;

use App\Models\SavingTransaction;
use App\Services\Cash\CashTransactionService;
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
            // 1) posting ke kas (INCOME) -> kategori sistem, cash autoposting akan skip
            $cashTx = $this->cashService->createIncome([
                'trx_date' => $data['trx_date'],
                'to_cash_account_id' => $data['cash_account_id'],
                'amount' => $data['amount'],
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
                'cash_account_id' => $data['cash_account_id'],
                'cash_transaction_id' => $cashTx->id,
                'description' => $data['description'] ?? null,
                'created_by' => $userId,
            ]);

            // autoposting jurnal simpanan
            $st->load(['cashAccount', 'savingProduct']);
            $this->autoPosting->postSavingTransaction($st);

            return $st;
        });
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
