<?php

namespace App\Services\Cash;

use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\Accounting\AutoPostingService;

class CashTransactionService
{
    public function __construct(
        private AutoPostingService $autoPosting
    ) {}
    public function createIncome(array $data, int $userId): CashTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1) create dulu pakai code placeholder yang pasti unik (uuid)
            $tx = CashTransaction::create([
                'code'               => 'TMP-' . Str::uuid()->toString(),
                'reference_code'     => $data['reference_code'] ?? null,
                'ref_number'         => $data['ref_number'] ?? null,
                'payment_method'     => $data['payment_method'] ?? null,
                'trx_date'           => $data['trx_date'],
                'type'               => 'INCOME',
                'to_cash_account_id' => $data['to_cash_account_id'],
                'cash_category_id'   => $data['cash_category_id'] ?? null,
                'category'           => $data['category'] ?? null,
                'description'        => $data['description'] ?? null,
                'amount'             => $data['amount'],
                'created_by'         => $userId,
            ]);

            // 2) update code final pakai ID (unik, no race condition)
            $tx->update([
                'code' => $this->finalCode('CK', $tx->trx_date, $tx->id),
            ]);

            // load relasi yang dipakai auto-posting (toCashAccount / fromCashAccount)
            $tx->load(['fromCashAccount', 'toCashAccount']);

            // auto jurnal (AutoPostingService boleh skip kalau kategori sistem)
            $this->autoPosting->postCashTransaction($tx);

            return $tx;
        });
    }

    private function finalCode(string $prefix, string $trxDate, int $id): string
    {
        // CK-YYYYMM-000123 (sequence = ID padded)
        $ym = date('Ym', strtotime($trxDate));
        return sprintf('%s-%s-%06d', $prefix, $ym, $id);
    }
    public function createExpense(array $data, int $userId): CashTransaction
    {
        return DB::transaction(function () use ($data, $userId) {

            // OPTIONAL (recommended): cegah saldo minus
            $balance = $this->getBalance($data['from_cash_account_id']);
            if ($balance < $data['amount']) {
                throw ValidationException::withMessages([
                    'amount' => "Saldo kas tidak cukup. Saldo saat ini: Rp " . number_format($balance, 0, ',', '.')
                ]);
            }

            $tx = CashTransaction::create([
                'code'                 => 'TMP-' . Str::uuid()->toString(),
                'reference_code'       => $data['reference_code'] ?? null,
                'ref_number'           => $data['ref_number'] ?? null,
                'payment_method'       => $data['payment_method'] ?? null,
                'trx_date'             => $data['trx_date'],
                'type'                 => 'EXPENSE',
                'from_cash_account_id' => $data['from_cash_account_id'],
                'cash_category_id'     => $data['cash_category_id'] ?? null,
                'category'             => $data['category'] ?? null,
                'description'          => $data['description'] ?? null,
                'amount'               => $data['amount'],
                'created_by'           => $userId,
            ]);

            $tx->update([
                'code' => $this->finalCode('CK', $tx->trx_date, $tx->id),
            ]);
            $tx->load(['fromCashAccount', 'toCashAccount']);
            $this->autoPosting->postCashTransaction($tx);
            return $tx;
        });
    }
    // Hitung saldo dari transaksi (INCOME/EXPENSE/TRANSFER)
    public function getBalance(int $cashAccountId): int
    {
        $income = CashTransaction::notVoided()
            ->where('type', 'INCOME')
            ->where('to_cash_account_id', $cashAccountId)->sum('amount');

        $expense = CashTransaction::notVoided()
            ->where('type', 'EXPENSE')
            ->where('from_cash_account_id', $cashAccountId)->sum('amount');

        $transferIn = CashTransaction::notVoided()
            ->where('type', 'TRANSFER')
            ->where('to_cash_account_id', $cashAccountId)->sum('amount');

        $transferOut = CashTransaction::notVoided()
            ->where('type', 'TRANSFER')
            ->where('from_cash_account_id', $cashAccountId)->sum('amount');

        return (int) $income + (int) $transferIn - (int) $expense - (int) $transferOut;
    }
    public function getAllBalances(): array
    {
        // 1 query: normalize transaksi jadi “movements” (delta + / -), lalu group by cash account
        $sql = <<<SQL
    WITH movements AS (
        SELECT to_cash_account_id AS cash_account_id, amount AS delta
        FROM cash_transactions
        WHERE type = 'INCOME' AND is_voided = false

        UNION ALL

        SELECT from_cash_account_id AS cash_account_id, -amount AS delta
        FROM cash_transactions
        WHERE type = 'EXPENSE' AND is_voided = false

        UNION ALL

        SELECT to_cash_account_id AS cash_account_id, amount AS delta
        FROM cash_transactions
        WHERE type = 'TRANSFER' AND is_voided = false

        UNION ALL

        SELECT from_cash_account_id AS cash_account_id, -amount AS delta
        FROM cash_transactions
        WHERE type = 'TRANSFER' AND is_voided = false
    )
    SELECT
        ca.id,
        ca.name,
        ca.type,
        COALESCE(SUM(m.delta), 0) AS balance
    FROM cash_accounts ca
    LEFT JOIN movements m ON m.cash_account_id = ca.id
    WHERE ca.is_active = true
    GROUP BY ca.id, ca.name, ca.type
    ORDER BY ca.type, ca.name;
    SQL;

        // DB::select returns array of stdClass
        return DB::select($sql);
    }
    public function createTransfer(array $data, int $userId): CashTransaction
    {
        return DB::transaction(function () use ($data, $userId) {

            if ((int)$data['from_cash_account_id'] === (int)$data['to_cash_account_id']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'to_cash_account_id' => 'Kas tujuan tidak boleh sama dengan kas sumber.',
                ]);
            }

            // OPTIONAL (recommended): cegah saldo minus
            $balance = $this->getBalance($data['from_cash_account_id']);
            if ($balance < $data['amount']) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => "Saldo kas tidak cukup. Saldo saat ini: Rp " . number_format($balance, 0, ',', '.')
                ]);
            }

            $tx = CashTransaction::create([
                'code'                 => 'TMP-' . Str::uuid()->toString(),
                'reference_code'       => $data['reference_code'] ?? null,
                'ref_number'           => $data['ref_number'] ?? null,
                'trx_date'             => $data['trx_date'],
                'type'                 => 'TRANSFER',
                'from_cash_account_id' => $data['from_cash_account_id'],
                'to_cash_account_id'   => $data['to_cash_account_id'],
                'cash_category_id'     => $data['cash_category_id'] ?? null,
                'category'             => $data['category'] ?? 'Transfer',
                'description'          => $data['description'] ?? null,
                'amount'               => $data['amount'],
                'created_by'           => $userId,
            ]);

            $tx->update([
                'code' => $this->finalCode('CK', $tx->trx_date, $tx->id),
            ]);
            $tx->load(['fromCashAccount', 'toCashAccount']);
            $this->autoPosting->postCashTransaction($tx);

            return $tx;
        });
    }
}
