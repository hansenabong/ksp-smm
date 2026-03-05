<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\Loans\LoanService;
use Illuminate\Http\Request;

class LoanPaymentController extends Controller
{
    public function create(Loan $loan)
    {
        $loan->load([
            'member',
            'loanProduct',
            'disbursedCashAccount',
            'application',
            'payments' => fn($q) => $q->notVoided()->orderBy('trx_date')->orderBy('id'),
        ]);

        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();

        // Build installment schedule
        $schedule = $this->buildSchedule($loan);

        // Penalty pre-calculation for the next installment
        $product              = $loan->loanProduct;
        $installmentNo        = $loan->payments->count() + 1;
        $expectedDueDate      = $loan->disbursed_date->copy()->addMonths($installmentNo);
        $penaltyDailyRateBp   = $product?->penalty_daily_rate_bp ?? 30;
        $earlyRepaymentRateBp = $product?->early_repayment_rate_bp ?? 500;

        return view('admin.loans.payments.create', compact(
            'loan', 'cashAccounts', 'schedule',
            'expectedDueDate', 'penaltyDailyRateBp', 'earlyRepaymentRateBp'
        ));
    }

    public function store(Request $request, Loan $loan, LoanService $service)
    {
        $validated = $request->validate([
            'trx_date'               => ['required', 'date'],
            'cash_account_id'        => ['required', 'exists:cash_accounts,id'],
            'amount'                 => ['required', 'integer', 'min:1'],
            'note'                   => ['nullable', 'string', 'max:255'],
            'penalty_days'           => ['nullable', 'integer', 'min:0'],
            'penalty_amount'         => ['nullable', 'integer', 'min:0'],
            'is_early_repayment'     => ['nullable', 'boolean'],
            'early_repayment_penalty' => ['nullable', 'integer', 'min:0'],
        ]);

        $payment = $service->payInstallment($loan->id, $validated, auth()->id());

        return redirect()->route('admin.loans.payments.receipt', $payment)
            ->with('success', 'Pembayaran angsuran berhasil disimpan.');
    }

    public function receipt(LoanPayment $payment)
    {
        $payment->load([
            'loan.member',
            'loan.loanProduct',
            'cashAccount',
            'creator',
        ]);

        return view('admin.loans.payments.receipt', compact('payment'));
    }

    /**
     * Build installment schedule array for the kartu angsuran view.
     * Each element: [no, due_date, amount, payment (LoanPayment|null)]
     */
    private function buildSchedule(Loan $loan): array
    {
        $payments  = $loan->payments->values(); // already loaded, notVoided, ordered
        $schedule  = [];
        $payIdx    = 0;

        $principalMonthly = intdiv($loan->principal, $loan->tenor_months);
        $lastPrincipal    = $loan->principal - ($principalMonthly * ($loan->tenor_months - 1));
        $interestMonthly  = $loan->interest_monthly  ?? 0;
        $adminMonthly     = $loan->admin_monthly      ?? 0;
        $insuranceMonthly = $loan->insurance_monthly  ?? 0;

        for ($i = 1; $i <= $loan->tenor_months; $i++) {
            $dueDate = $loan->disbursed_date->copy()->addMonths($i);

            if ($i < $loan->tenor_months) {
                $amount = $loan->monthly_installment;
            } else {
                // Last installment absorbs principal remainder
                $amount = $lastPrincipal + $interestMonthly + $adminMonthly + $insuranceMonthly;
            }

            $payment = $payIdx < $payments->count() ? $payments[$payIdx++] : null;

            $schedule[] = [
                'no'      => $i,
                'due'     => $dueDate,
                'amount'  => $amount,
                'payment' => $payment,
            ];
        }

        return $schedule;
    }
}
