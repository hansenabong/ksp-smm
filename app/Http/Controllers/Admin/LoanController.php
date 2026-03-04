<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;

class LoanController extends Controller
{
    public function index()
    {
        $totals = Loan::where('status', 'ACTIVE')
            ->selectRaw('
                COALESCE(SUM(principal), 0)   AS total_principal,
                COALESCE(SUM(total_paid), 0)  AS total_paid,
                COALESCE(SUM(outstanding), 0) AS total_outstanding
            ')
            ->first();

        $loans = Loan::with(['member', 'loanProduct'])
            ->where('status', 'ACTIVE')
            ->orderByDesc('disbursed_date')
            ->paginate(10)
            ->withQueryString();

        return view('admin.loans.active.index', [
            'loans'            => $loans,
            'totalPrincipal'   => $totals->total_principal,
            'totalPaid'        => $totals->total_paid,
            'totalOutstanding' => $totals->total_outstanding,
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
}
