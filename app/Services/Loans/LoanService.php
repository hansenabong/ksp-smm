<?php

namespace App\Services\Loans;

use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\LoanPayment;
use App\Services\Cash\CashTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\Accounting\AutoPostingService;

class LoanService
{
    public function __construct(
        private CashTransactionService $cash,
        private AutoPostingService $autoPosting
    ) {}

    public function approveAndDisburse(int $applicationId, array $data, int $userId): Loan
    {
        return DB::transaction(function () use ($applicationId, $data, $userId) {

            $app = LoanApplication::lockForUpdate()->findOrFail($applicationId);

            if ($app->status !== 'PENDING') {
                throw ValidationException::withMessages(['status' => 'Pengajuan sudah diproses.']);
            }

            // hitung komponen (flat)
            $principal = (int) $app->principal;
            $tenor = (int) $app->tenor_months;
            $rate = (float) $app->interest_monthly_percent;

            // pokok per bulan (rata, sisa dibebankan di angsuran terakhir)
            $principalMonthly = intdiv($principal, $tenor);

            $interestMonthly = (int) round($principal * ($rate / 100.0));
            $adminMonthly = (int) $app->admin_monthly;
            $insuranceMonthly = (int) $app->insurance_monthly;

            $monthlyInstallment = $principalMonthly + $interestMonthly + $adminMonthly + $insuranceMonthly;
            // total = (n-1 angsuran biasa) + (angsuran terakhir dengan sisa pokok)
            $lastPrincipal = $principal - ($principalMonthly * ($tenor - 1));
            $totalPayable = ($monthlyInstallment * ($tenor - 1))
                          + ($lastPrincipal + $interestMonthly + $adminMonthly + $insuranceMonthly);
            $fromCashId = (int) $data['from_cash_account_id'];

            // cek saldo kas real-time
            $balance = $this->cash->getBalance($fromCashId);

            if ($balance < $principal) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'from_cash_account_id' => 'Saldo kas tidak cukup untuk pencairan. Saldo: Rp '
                        . number_format($balance, 0, ',', '.')
                        . ' | Dibutuhkan: Rp '
                        . number_format($principal, 0, ',', '.'),
                ]);
            }
            // posting kas untuk pencairan (uang keluar)
            $cashTx = $this->cash->createExpense([
                'trx_date' => $data['trx_date'],
                'from_cash_account_id' => $data['from_cash_account_id'],
                'amount' => $principal, // biasanya yang keluar = pokok pinjaman
                'category' => 'Pencairan Pinjaman',
                'description' => $data['approval_note'] ?? 'Pencairan pinjaman',
            ], $userId);

            // update pengajuan
            $app->update([
                'status' => 'APPROVED',
                'approval_note' => $data['approval_note'] ?? null,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approved_cash_account_id' => $data['from_cash_account_id'],
            ]);

            // create pinjaman aktif
            $loan = Loan::create([
                'loan_application_id' => $app->id,
                'loan_product_id' => $app->loan_product_id,            // PENTING biar mapping akun ada
                'member_id' => $app->member_id,
                'disbursed_date' => $data['trx_date'],
                'principal' => $principal,
                'tenor_months' => $tenor,
                'interest_monthly_percent' => $rate,
                'principal_monthly' => $principalMonthly,
                'interest_monthly' => $interestMonthly,
                'admin_monthly' => $adminMonthly,
                'insurance_monthly' => $insuranceMonthly,
                'monthly_installment' => $monthlyInstallment,
                'total_payable' => $totalPayable,
                'total_paid' => 0,
                'outstanding' => $totalPayable,
                'status' => 'ACTIVE',
                'disbursed_cash_account_id' => $data['from_cash_account_id'],
                'disbursed_cash_transaction_id' => $cashTx->id,
                'created_by' => $userId,
            ]);

            //  Autoposting jurnal pencairan: DR Piutang, CR Kas
            $loan->load(['loanProduct', 'disbursedCashAccount']);
            $this->autoPosting->postLoanDisbursement($loan);

            return $loan;
        });
    }

    public function reject(int $applicationId, string $note, int $userId): void
    {
        DB::transaction(function () use ($applicationId, $note, $userId) {
            $app = LoanApplication::lockForUpdate()->findOrFail($applicationId);

            if ($app->status !== 'PENDING') {
                throw ValidationException::withMessages(['status' => 'Pengajuan sudah diproses.']);
            }

            $app->update([
                'status' => 'REJECTED',
                'approval_note' => $note,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
        });
    }

    public function payInstallment(int $loanId, array $data, int $userId): LoanPayment
    {
        return DB::transaction(function () use ($loanId, $data, $userId) {
            $loan = Loan::with('loanProduct')->lockForUpdate()->findOrFail($loanId);

            if ($loan->status !== 'ACTIVE') {
                throw ValidationException::withMessages(['status' => 'Pinjaman sudah tidak aktif.']);
            }

            $amount           = (int) $data['amount'];
            $isEarlyRepayment = !empty($data['is_early_repayment']);

            if ($amount > $loan->outstanding) {
                throw ValidationException::withMessages([
                    'amount' => 'Pembayaran melebihi sisa pinjaman. Outstanding: Rp '
                        . number_format($loan->outstanding, 0, ',', '.'),
                ]);
            }

            // --- Penalty calculation ---
            $penaltyDays            = (int) ($data['penalty_days'] ?? 0);
            $penaltyAmount          = (int) ($data['penalty_amount'] ?? 0);
            $earlyRepaymentPenalty  = (int) ($data['early_repayment_penalty'] ?? 0);

            // Auto-compute penalty if not overridden by form
            $product = $loan->loanProduct;
            if ($product) {
                // Determine installment number = non-voided payments so far + 1
                $installmentNo  = LoanPayment::notVoided()->where('loan_id', $loan->id)->count() + 1;
                $expectedDue    = \Carbon\Carbon::parse($loan->disbursed_date)->addMonths($installmentNo);
                $trxDate        = \Carbon\Carbon::parse($data['trx_date']);

                // Late penalty (only if data doesn't explicitly pass penalty_days=0 with intent)
                if (!isset($data['penalty_days']) && $trxDate->gt($expectedDue)) {
                    $penaltyDays   = (int) $trxDate->diffInDays($expectedDue);
                    $penaltyAmount = (int) round($loan->monthly_installment * $penaltyDays * $product->penalty_daily_rate);
                }

                // Early repayment penalty
                if ($isEarlyRepayment && !isset($data['early_repayment_penalty'])) {
                    $earlyRepaymentPenalty = (int) round($loan->outstanding * $product->early_repayment_rate);
                }
            }

            // Total cash received from member
            $totalCash = $amount + $penaltyAmount + $earlyRepaymentPenalty;

            // posting kas pemasukan (total cash received)
            $cashTx = $this->cash->createIncome([
                'trx_date'            => $data['trx_date'],
                'to_cash_account_id'  => $data['cash_account_id'],
                'amount'              => $totalCash,
                'category'            => $isEarlyRepayment ? 'Pelunasan Pinjaman' : 'Angsuran Pinjaman',
                'description'         => $data['note'] ?? ($isEarlyRepayment ? 'Pelunasan dipercepat' : 'Pembayaran angsuran'),
            ], $userId);

            $payment = LoanPayment::create([
                'loan_id'                => $loan->id,
                'trx_date'               => $data['trx_date'],
                'amount'                 => $amount,
                'cash_account_id'        => $data['cash_account_id'],
                'cash_transaction_id'    => $cashTx->id,
                'note'                   => $data['note'] ?? null,
                'created_by'             => $userId,
                'penalty_days'           => $penaltyDays,
                'penalty_amount'         => $penaltyAmount,
                'is_early_repayment'     => $isEarlyRepayment,
                'early_repayment_penalty' => $earlyRepaymentPenalty,
            ]);

            // update totals (outstanding reduced by installment amount only)
            $loan->total_paid += $amount;
            $loan->outstanding = max(0, $loan->total_payable - $loan->total_paid);

            if ($loan->outstanding === 0) {
                $loan->status = 'CLOSED';
            }

            $loan->save();

            $payment->load(['loan.loanProduct', 'cashAccount']);
            $this->autoPosting->postLoanPayment($payment);

            return $payment;
        });
    }
    public function voidPayment(int $paymentId, string $reason, int $userId): void
    {
        DB::transaction(function () use ($paymentId, $reason, $userId) {

            $payment = LoanPayment::lockForUpdate()->findOrFail($paymentId);

            if ($payment->is_voided) {
                throw ValidationException::withMessages(['void' => 'Pembayaran sudah di-void.']);
            }

            $loan = Loan::lockForUpdate()->findOrFail($payment->loan_id);

            // void payment
            $payment->forceFill([
                'is_voided' => true,
                'void_reason' => $reason,
                'voided_by' => $userId,
                'voided_at' => now(),
            ])->save();

            // void linked cash tx
            if ($payment->cash_transaction_id) {
                $cashTx = \App\Models\CashTransaction::lockForUpdate()->find($payment->cash_transaction_id);
                if ($cashTx && !$cashTx->is_voided) {
                    $cashTx->forceFill([
                        'is_voided' => true,
                        'void_reason' => "VOID Angsuran #{$payment->id}: " . $reason,
                        'voided_by' => $userId,
                        'voided_at' => now(),
                    ])->save();
                }
            }

            // void journal entries for both the payment and linked cash tx
            $this->autoPosting->voidBySource('loan_payments', $payment->id, $reason);
            if ($payment->cash_transaction_id) {
                $this->autoPosting->voidBySource('cash_transactions', $payment->cash_transaction_id, $reason);
            }

            // recompute totals from non-void payments
            $totalPaid = LoanPayment::notVoided()->where('loan_id', $loan->id)->sum('amount');
            $loan->total_paid = (int) $totalPaid;
            $loan->outstanding = max(0, $loan->total_payable - $loan->total_paid);
            $loan->status = ($loan->outstanding === 0) ? 'CLOSED' : 'ACTIVE';
            $loan->save();
        });
    }
}
