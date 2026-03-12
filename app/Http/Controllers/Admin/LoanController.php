<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\Accounting\AutoPostingService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(private AutoPostingService $autoPosting) {}

    public function index()
    {
        $totalPrincipal = (int) Loan::where('status', 'ACTIVE')->sum('principal');

        $loans = Loan::with(['member', 'loanProduct'])
            ->where('status', 'ACTIVE')
            ->orderByDesc('disbursed_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.loans.active.index', [
            'loans'          => $loans,
            'totalPrincipal' => $totalPrincipal,
        ]);
    }

    public function show(Loan $loan)
    {
        $loan->load([
            'member',
            'loanProduct',
            'disbursedCashAccount',
            'creator',
            'application.files.uploader',
            'payments' => fn($q) => $q->notVoided()->with(['cashAccount', 'creator'])->orderByDesc('trx_date'),
        ]);
        return view('admin.loans.active.show', compact('loan'));
    }

    public function schedule(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application']);
        [$schedule, $paidMap] = $this->buildSchedule($loan);
        return view('admin.loans.active.schedule', compact('loan', 'schedule', 'paidMap'));
    }

    public function schedulePdf(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application']);
        [$schedule, $paidMap] = $this->buildSchedule($loan);
        $pdf = Pdf::loadView('admin.loans.active.schedule-pdf', compact('loan', 'schedule', 'paidMap'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('jadwal-angsuran-' . $loan->id . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────
    //  PERMOHONAN DANA
    // ─────────────────────────────────────────────────────────────

    public function permohonanDana(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application', 'disbursedCashAccount', 'creator']);
        $fees = $loan->disbursement_fees ?? [];
        return view('admin.loans.active.permohonan-dana', compact('loan', 'fees'));
    }

    public function permohonanDanaPdf(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application', 'disbursedCashAccount', 'creator']);
        $fees = $loan->disbursement_fees ?? [];

        $pdf = Pdf::loadView('admin.loans.active.permohonan-dana-pdf', compact('loan', 'fees'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('permohonan-dana-' . $loan->id . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────
    //  KWITANSI PLAFON
    // ─────────────────────────────────────────────────────────────

    public function kwitansiPlafon(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application', 'disbursedCashAccount', 'creator']);
        return view('admin.loans.active.kwitansi-plafon', compact('loan'));
    }

    // ─────────────────────────────────────────────────────────────
    //  KWITANSI PENCAIRAN
    // ─────────────────────────────────────────────────────────────

    private const FEE_FIELDS = [
        'biaya_administrasi', 'biaya_provisi', 'biaya_notaris', 'asuransi',
        'pengecekan', 'ploting', 'roya', 'pnpb', 'hold_imb', 'hold_pbb',
        'hold_angsuran', 'bphtb', 'pph',
    ];

    public function kwitansiPencairan(Loan $loan)
    {
        $loan->load(['member', 'loanProduct', 'application', 'disbursedCashAccount', 'creator']);
        $fees = $loan->disbursement_fees ?? [];
        return view('admin.loans.active.kwitansi-pencairan', compact('loan', 'fees'));
    }

    public function saveKwitansiPencairan(Loan $loan, Request $request)
    {
        $fees = [];
        foreach (self::FEE_FIELDS as $field) {
            $raw = preg_replace('/[^0-9]/', '', $request->input($field, ''));
            $val = (int) $raw;
            if ($val > 0) {
                $fees[$field] = $val;
            }
        }
        $loan->update(['disbursement_fees' => $fees ?: null]);

        // Post supplementary GL entry for fees (idempotent — skips if already posted)
        if ($fees) {
            $loan->load(['loanProduct', 'disbursedCashAccount']);
            $this->autoPosting->postLoanDisbursementFees($loan);
        }

        return redirect()
            ->route('admin.loans.active.kwitansi.pencairan', $loan)
            ->with('success', 'Biaya pencairan berhasil disimpan.');
    }

    // ─────────────────────────────────────────────────────────────
    //  SETTLEMENT — Perhitungan Pelunasan
    // ─────────────────────────────────────────────────────────────

    public function settlement(Loan $loan, Request $request)
    {
        $loan->load(['member', 'loanProduct', 'application']);
        $calc = $this->buildSettlement($loan, $request->input('settlement_date') ?: now()->toDateString());
        $settlementDate = $calc['settlement_date']->toDateString();
        return view('admin.loans.active.settlement', compact('loan', 'calc', 'settlementDate'));
    }

    public function settlementPdf(Loan $loan, Request $request)
    {
        $loan->load(['member', 'loanProduct', 'application']);
        $calc = $this->buildSettlement($loan, $request->input('settlement_date', now()->toDateString()));
        $pdf = Pdf::loadView('admin.loans.active.settlement-pdf', compact('loan', 'calc'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('pelunasan-' . $loan->id . '-' . $calc['settlement_date']->format('Y-m-d') . '.pdf');
    }

    private function buildSettlement(Loan $loan, string $dateStr): array
    {
        $settlementDate   = Carbon::parse($dateStr);
        $disbursedDate    = Carbon::parse($loan->disbursed_date);
        $principal        = (int) $loan->principal;
        $tenor            = (int) $loan->tenor_months;
        $principalMonthly = (int) $loan->principal_monthly;
        $interestMonthly  = (int) $loan->interest_monthly;
        $adminMonthly     = (int) $loan->admin_monthly;
        $insuranceMonthly = (int) $loan->insurance_monthly;
        $monthlyInstallment = (int) $loan->monthly_installment;

        $product              = $loan->loanProduct;
        $penaltyDailyRateBp   = $product?->penalty_daily_rate_bp ?? 30;   // default 0.30%/day
        $earlyRepaymentRateBp = $product?->early_repayment_rate_bp ?? 500; // default 5%

        // Payments made
        $payments   = $loan->payments()->notVoided()->orderBy('trx_date')->orderBy('id')->get();
        $paidCount  = $payments->count();
        $remaining  = $tenor - $paidCount;

        // Sisa pokok hutang (outstanding principal only)
        // For flat rate: we reduce principal equally each period
        $sisaPokok = $principal - ($principalMonthly * min($paidCount, $tenor - 1));
        if ($paidCount >= $tenor - 1) {
            // last period: residual
            $sisaPokok = max(0, $principal - ($principalMonthly * $paidCount));
        }

        // Expected due date for next installment
        $nextInstallmentNo  = $paidCount + 1;
        $expectedDueDate    = $disbursedDate->copy()->addMonths($nextInstallmentNo);

        // Overdue installments = installments whose due date has passed but unpaid
        $overdueCount = 0;
        $tunggakan    = 0;
        for ($i = 1; $i <= $tenor; $i++) {
            $due = $disbursedDate->copy()->addMonths($i);
            if ($due->lte($settlementDate) && $i > $paidCount) {
                $overdueCount++;
                $installmentAmount = ($i === $tenor)
                    ? ($principal - ($principalMonthly * ($tenor - 1))) + $interestMonthly + $adminMonthly + $insuranceMonthly
                    : $monthlyInstallment;
                $tunggakan += $installmentAmount;
            }
        }

        // Late penalty: per overdue installment, days late from its due date
        $dendaKeterlambatan = 0;
        $dendaDetails       = [];
        for ($i = $paidCount + 1; $i <= $paidCount + $overdueCount; $i++) {
            $due      = $disbursedDate->copy()->addMonths($i);
            $daysLate = max(0, $due->diffInDays($settlementDate, false));
            if ($daysLate > 0) {
                $installmentAmount = ($i === $tenor)
                    ? ($principal - ($principalMonthly * ($tenor - 1))) + $interestMonthly + $adminMonthly + $insuranceMonthly
                    : $monthlyInstallment;
                $penalty = (int) round($installmentAmount * ($penaltyDailyRateBp / 10000.0) * $daysLate);
                $dendaKeterlambatan += $penalty;
                $dendaDetails[] = [
                    'installment_no' => $i,
                    'due_date'       => $due,
                    'days_late'      => $daysLate,
                    'base_amount'    => $installmentAmount,
                    'penalty'        => $penalty,
                ];
            }
        }

        // Early repayment penalty (biaya pelunasan dipercepat)
        $isEarlyRepayment      = $remaining > 0;
        $biayaPelunasanDipercepat = 0;
        if ($isEarlyRepayment) {
            $biayaPelunasanDipercepat = (int) round($sisaPokok * ($earlyRepaymentRateBp / 10000.0));
        }

        // Remaining interest (bunga tersisa for flat rate)
        $bungaTersisa    = $interestMonthly * $remaining;
        $adminTersisa    = $adminMonthly    * $remaining;
        $asuransiTersisa = $insuranceMonthly * $remaining;

        // Current month installment (bulan berjalan angsuran)
        $angsuranBulanBerjalan = ($paidCount < $tenor) ? $monthlyInstallment : 0;

        // TOTAL PELUNASAN
        $totalPelunasan = $sisaPokok
            + $bungaTersisa
            + $adminTersisa
            + $asuransiTersisa
            + $tunggakan        // overdue principal component already in sisa pokok, but denda tambahan
            + $dendaKeterlambatan
            + $biayaPelunasanDipercepat;

        // Subtract what's included in sisa pokok to avoid double-counting tunggakan principal
        // Actually tunggakan is the full overdue installment amounts already (principal+interest+admin)
        // For flat rate settlement, the standard is: remaining total_payable + penalties
        $totalPelunasanStandard = max(0, (int)$loan->outstanding)
            + $dendaKeterlambatan
            + $biayaPelunasanDipercepat;

        return [
            'settlement_date'           => $settlementDate,
            'paid_count'                => $paidCount,
            'remaining_periods'         => $remaining,
            'sisa_pokok'                => $sisaPokok,
            'angsuran_bulan_berjalan'   => $angsuranBulanBerjalan,
            'expected_due_date'         => $expectedDueDate,
            'overdue_count'             => $overdueCount,
            'tunggakan'                 => $tunggakan,
            'denda_keterlambatan'       => $dendaKeterlambatan,
            'denda_details'             => $dendaDetails,
            'penalty_daily_rate_bp'     => $penaltyDailyRateBp,
            'is_early_repayment'        => $isEarlyRepayment,
            'biaya_pelunasan_dipercepat'=> $biayaPelunasanDipercepat,
            'early_repayment_rate_bp'   => $earlyRepaymentRateBp,
            'bunga_tersisa'             => $bungaTersisa,
            'admin_tersisa'             => $adminTersisa,
            'asuransi_tersisa'          => $asuransiTersisa,
            'outstanding'               => (int) $loan->outstanding,
            'total_pelunasan'           => $totalPelunasanStandard,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  SUBLEDGER DETAIL — Kartu Piutang / Ledger per Pinjaman
    // ─────────────────────────────────────────────────────────────

    public function subledgerPdf(Loan $loan)
    {
        $loan->load([
            'member', 'loanProduct', 'application',
            'payments' => fn($q) => $q->notVoided()
                ->with(['cashTransaction', 'cashAccount'])
                ->orderBy('trx_date')->orderBy('id'),
        ]);

        $principal        = (int) $loan->principal;
        $tenor            = (int) $loan->tenor_months;
        $principalMonthly = (int) $loan->principal_monthly;
        $interestMonthly  = (int) $loan->interest_monthly;
        $adminMonthly     = (int) $loan->admin_monthly;
        $insuranceMonthly = (int) $loan->insurance_monthly;
        $disbursedDate    = Carbon::parse($loan->disbursed_date);

        $payments = $loan->payments->values();

        // Build subledger rows
        $rows    = [];
        $balance = 0; // running receivable balance

        // Row 0 — disbursement
        $balance = (int) $loan->total_payable;
        $rows[]  = [
            'no'          => 0,
            'flag'        => 'L',
            'doc_date'    => $disbursedDate->copy(),
            'doc_no'      => $loan->application?->notaris_deed_number
                           ?? ('KSP-LOAN-' . $loan->id),
            'payterm'     => 0,
            'amount'      => $loan->total_payable,
            'payment'     => 0,
            'balance'     => $balance,
            'description' => $loan->member?->name ?? 'Pencairan',
            'is_disburse' => true,
        ];

        for ($n = 1; $n <= $tenor; $n++) {
            $isLast      = ($n === $tenor);
            $recovery    = $isLast
                ? ($principal - ($principalMonthly * ($tenor - 1)))
                : $principalMonthly;
            $installment = $recovery + $interestMonthly + $adminMonthly + $insuranceMonthly;
            $dueDate     = $disbursedDate->copy()->addMonths($n);

            $actualPayment = ($n <= $payments->count()) ? $payments[$n - 1] : null;
            $paidAmount    = $actualPayment ? (int) $actualPayment->amount : 0;
            $balance      -= $paidAmount;

            $rows[] = [
                'no'          => $n,
                'flag'        => $actualPayment ? 'L' : 'O',
                'doc_date'    => $dueDate,
                'doc_no'      => $actualPayment?->cashTransaction?->code ?? '',
                'payterm'     => $n,
                'amount'      => $installment,
                'payment'     => $paidAmount,
                'balance'     => max(0, $balance),
                'description' => 'Installment ' . $n,
                'is_disburse' => false,
                'payment_obj' => $actualPayment,
            ];

            if ($actualPayment === null) {
                // unpaid — balance doesn't change
                $balance += $paidAmount; // noop (0), just explicit
            }
        }

        $totalAmount  = collect($rows)->skip(1)->sum('amount');
        $totalPayment = collect($rows)->skip(1)->sum('payment');
        $totalPenalty = collect($rows)->skip(1)->sum(function ($row) {
            $p = $row['payment_obj'] ?? null;
            return $p ? ((int)($p->penalty_amount ?? 0) + (int)($p->early_repayment_penalty ?? 0)) : 0;
        });

        $pdf = Pdf::loadView('admin.loans.active.subledger-pdf', compact(
            'loan', 'rows', 'totalAmount', 'totalPayment', 'totalPenalty'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('subledger-' . $loan->id . '.pdf');
    }

    private function buildSchedule(Loan $loan): array
    {
        $principal        = (int) $loan->principal;
        $tenor            = (int) $loan->tenor_months;
        $principalMonthly = (int) $loan->principal_monthly;
        $interestMonthly  = (int) $loan->interest_monthly;
        $adminMonthly     = (int) $loan->admin_monthly;
        $insuranceMonthly = (int) $loan->insurance_monthly;
        $disbursedDate    = Carbon::parse($loan->disbursed_date);

        $initialUnearned    = $interestMonthly * $tenor;
        $initialReceivable  = $principal + $initialUnearned;

        $schedule = [];

        // Row 0 — disbursement (no payment)
        $schedule[] = [
            'per'                     => 0,
            'due_date'                => $disbursedDate->copy(),
            'loan_payment'            => 0,
            'loan_income'             => 0,
            'recovery'                => 0,
            'outstanding_principal'   => $principal,
            'unearned_loan_income'    => $initialUnearned,
            'loan_contract_receivable'=> $initialReceivable,
            'blv_pct'                 => 100.0,
            'is_row_zero'             => true,
        ];

        $outstandingPrincipal = $principal;
        $unearnedIncome       = $initialUnearned;

        for ($n = 1; $n <= $tenor; $n++) {
            $isLast   = ($n === $tenor);
            $recovery = $isLast
                ? ($principal - $principalMonthly * ($tenor - 1))
                : $principalMonthly;

            $loanPayment = $recovery + $interestMonthly + $adminMonthly + $insuranceMonthly;

            $outstandingPrincipal -= $recovery;
            $unearnedIncome       -= $interestMonthly;
            $receivable            = $outstandingPrincipal + $unearnedIncome;
            $blvPct                = $initialReceivable > 0
                ? round($receivable / $initialReceivable * 100, 4)
                : 0.0;

            $schedule[] = [
                'per'                     => $n,
                'due_date'                => $disbursedDate->copy()->addMonths($n),
                'loan_payment'            => $loanPayment,
                'loan_income'             => $interestMonthly,
                'recovery'                => $recovery,
                'outstanding_principal'   => max(0, $outstandingPrincipal),
                'unearned_loan_income'    => max(0, $unearnedIncome),
                'loan_contract_receivable'=> max(0, $receivable),
                'blv_pct'                 => max(0.0, $blvPct),
                'is_row_zero'             => false,
            ];
        }

        // Map actual non-voided payments by installment index (1-based, ordered by trx_date asc)
        $payments = $loan->payments()
            ->notVoided()
            ->orderBy('trx_date')
            ->orderBy('id')
            ->get();

        $paidMap = [];
        foreach ($payments as $idx => $payment) {
            $paidMap[$idx + 1] = $payment; // index 1 = first installment
        }

        return [$schedule, $paidMap];
    }
}
