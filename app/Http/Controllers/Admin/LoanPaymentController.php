<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Services\Loans\LoanService;
use Carbon\Carbon;
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

        // Penalty pre-calculation
        $product              = $loan->loanProduct;
        $paidCount            = $loan->payments->count();
        $installmentNo        = $paidCount + 1;
        $expectedDueDate      = $loan->disbursed_date->copy()->addMonths($installmentNo);
        $penaltyDailyRateBp   = $product?->penalty_daily_rate_bp ?? 30;
        $earlyRepaymentRateBp = $product?->early_repayment_rate_bp ?? 500;

        // Last installment must use outstanding to absorb any rounding remainder
        $isLastSlot     = $installmentNo >= $loan->tenor_months;
        $installmentAmt = $isLastSlot ? (int) $loan->outstanding : (int) $loan->monthly_installment;

        // Accumulated penalty — denda akumulasi atas angsuran tertunggak
        $accPenalty = $this->calcAccumulatedPenalty($loan, $paidCount, $penaltyDailyRateBp);

        // Total denda that has already been collected across all payments
        $totalDendaCollected = (int) $loan->payments->sum('penalty_amount')
                             + (int) $loan->payments->sum('early_repayment_penalty');

        return view('admin.loans.payments.create', compact(
            'loan', 'cashAccounts', 'schedule',
            'expectedDueDate', 'penaltyDailyRateBp', 'earlyRepaymentRateBp',
            'accPenalty', 'installmentAmt', 'totalDendaCollected'
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

        // Determine which installment slot this payment fills.
        // Mirrors buildSchedule(): not-voided payments ordered by trx_date, id
        // are matched 1-to-1 with installment slots.
        $orderedIds = LoanPayment::where('loan_id', $payment->loan_id)
            ->where('is_voided', false)
            ->orderBy('trx_date')
            ->orderBy('id')
            ->pluck('id');

        $idx = $orderedIds->search($payment->id);
        $installmentNo = $idx === false ? null : $idx + 1;
        $totalTenor    = $payment->loan->tenor_months;

        return view('admin.loans.payments.receipt', compact('payment', 'installmentNo', 'totalTenor'));
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
        $insuranceMonthly = $loan->insurance_monthly  ?? 0;

        for ($i = 1; $i <= $loan->tenor_months; $i++) {
            $dueDate = $loan->disbursed_date->copy()->addMonths($i);

            if ($i < $loan->tenor_months) {
                $amount = $loan->monthly_installment;
            } else {
                // Last installment absorbs principal remainder
                $amount = $lastPrincipal + $interestMonthly + $insuranceMonthly;
            }

            $payment = $payIdx < $payments->count() ? $payments[$payIdx++] : null;
            $isLate  = $payment !== null && Carbon::parse($payment->trx_date)->gt($dueDate);

            $schedule[] = [
                'no'      => $i,
                'due'     => $dueDate,
                'amount'  => $amount,
                'payment' => $payment,
                'is_late' => $isLate,
            ];
        }

        return $schedule;
    }

    /**
     * Hitung denda akumulasi:
     * - Angsuran sudah dibayar tapi terlambat & belum ada penalty_amount → denda fixed (pakai trx_date)
     * - Angsuran belum dibayar & sudah lewat jatuh tempo → denda dinamis (pakai $asOf)
     */
    public static function calcAccumulatedPenalty(Loan $loan, ?int $paidCount = null, ?int $dailyRateBp = null, ?Carbon $asOf = null): array
    {
        $asOf          = $asOf ?? Carbon::today();
        $disbursedDate = Carbon::parse($loan->disbursed_date);
        $tenor         = (int) $loan->tenor_months;
        $dailyRateBp   = $dailyRateBp ?? ($loan->loanProduct?->penalty_daily_rate_bp ?? 30);
        $installment   = (int) $loan->monthly_installment;

        // Use already-loaded relation when available, otherwise query
        if ($loan->relationLoaded('payments')) {
            $payments = $loan->payments->values(); // notVoided already filtered in the load
        } else {
            $payments = $loan->payments()->where('is_voided', false)
                ->orderBy('trx_date')->orderBy('id')->get();
        }

        $totalDenda   = 0;
        $overdueCount = 0;
        $details      = [];

        for ($i = 1; $i <= $tenor; $i++) {
            $dueDate = $disbursedDate->copy()->addMonths($i);
            $payment = $payments->get($i - 1); // 0-indexed slot

            if ($payment) {
                if ($payment->is_early_repayment ?? false) break; // pelunasan ends the loan
                $payDate  = Carbon::parse($payment->trx_date);
                $penaltyCollected = (int)($payment->penalty_amount ?? 0) > 0;
                if ($payDate->gt($dueDate) && !$penaltyCollected) {
                    $daysLate = (int) $dueDate->diffInDays($payDate);
                    $penalty  = (int) round($installment * $daysLate * ($dailyRateBp / 10000));
                    if ($penalty > 0) {
                        $overdueCount++;
                        $totalDenda += $penalty;
                        $details[] = ['no' => $i, 'due_date' => $dueDate, 'days_late' => $daysLate, 'penalty' => $penalty];
                    }
                }
            } else {
                if ($dueDate->gt($asOf)) break; // not yet due
                $daysLate = (int) $dueDate->diffInDays($asOf);
                if ($daysLate <= 0) continue;
                $penalty  = (int) round($installment * $daysLate * ($dailyRateBp / 10000));
                $overdueCount++;
                $totalDenda += $penalty;
                $details[] = ['no' => $i, 'due_date' => $dueDate, 'days_late' => $daysLate, 'penalty' => $penalty];
            }
        }

        return [
            'total'         => $totalDenda,
            'overdue_count' => $overdueCount,
            'details'       => $details,
        ];
    }
}
