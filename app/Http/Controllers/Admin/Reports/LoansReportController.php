<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoansReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'ACTIVE');

        $baseQuery = Loan::query()
            ->when($status && $status !== 'ALL', fn($q) => $q->where('status', $status));

        $totals = (clone $baseQuery)
            ->selectRaw('
                COUNT(*) AS total_count,
                COALESCE(SUM(principal), 0)   AS total_principal,
                COALESCE(SUM(total_paid), 0)  AS total_paid,
                COALESCE(SUM(outstanding), 0) AS total_outstanding
            ')
            ->first();

        $loans = (clone $baseQuery)
            ->with(['member', 'disbursedCashAccount', 'loanProduct'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.loans.index', compact('loans', 'status', 'totals'));
    }

    public function export(Request $request)
    {
        $status = $request->input('status', 'ACTIVE');

        $q = Loan::query()
            ->with(['member', 'disbursedCashAccount'])
            ->when($status && $status !== 'ALL', fn($q) => $q->where('status', $status))
            ->orderByDesc('id');

        $filename = "report_loans_" . strtolower($status ?: 'all') . ".csv";

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'loan_id',
                'member_no',
                'member_name',
                'principal',
                'tenor_months',
                'monthly_installment',
                'total_payable',
                'total_paid',
                'outstanding',
                'cash_account',
                'status',
            ], ';');

            foreach ($q->cursor() as $loan) {
                fputcsv($out, [
                    $loan->id,
                    $loan->member?->member_no ?? '',
                    $loan->member?->name ?? '',
                    (int)($loan->principal ?? 0),
                    (int)($loan->tenor_months ?? 0),
                    (int)($loan->monthly_installment ?? 0),
                    (int)($loan->total_payable ?? 0),
                    (int)($loan->total_paid ?? 0),
                    (int)($loan->outstanding ?? 0),
                    $loan->disbursedCashAccount?->name ?? '',
                    $loan->status,
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
