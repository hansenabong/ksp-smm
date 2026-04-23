<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomeStatementController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'show_zero' => ['nullable', 'boolean'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo   = $validated['date_to']   ?? now()->toDateString();
        $showZero = (bool) ($validated['show_zero'] ?? false);

        // Fetch period debit/credit for all leaf REVENUE and EXPENSE accounts.
        // "Period" = transactions within [date_from, date_to].
        $accounts = Account::query()
            ->whereIn('accounts.type', ['REVENUE', 'EXPENSE'])
            ->whereDoesntHave('children')
            ->leftJoin('journal_lines as jl', 'jl.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.normal_balance',
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS period_debit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS period_credit"),
            ])
            ->addBinding([$dateFrom, $dateTo, $dateFrom, $dateTo], 'select')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($r) {
                $r->period_debit  = (int) $r->period_debit;
                $r->period_credit = (int) $r->period_credit;
                // REVENUE: credit-normal → net = credit − debit
                // EXPENSE: debit-normal  → net = debit − credit
                $r->amount = $r->normal_balance === 'C'
                    ? $r->period_credit - $r->period_debit
                    : $r->period_debit  - $r->period_credit;
                return $r;
            });

        if (!$showZero) {
            $accounts = $accounts->filter(fn($r) => $r->amount !== 0 || $r->period_debit > 0 || $r->period_credit > 0)->values();
        }

        $revenues = $accounts->where('type', 'REVENUE')->values();
        $expenses = $accounts->where('type', 'EXPENSE')->values();

        $totalRevenue = (int) $revenues->sum('amount');
        $totalExpense = (int) $expenses->sum('amount');
        $netIncome    = $totalRevenue - $totalExpense;
        $isProfit     = $netIncome >= 0;

        return view('admin.accounting.income_statement.index', compact(
            'dateFrom', 'dateTo', 'showZero',
            'revenues', 'expenses',
            'totalRevenue', 'totalExpense', 'netIncome', 'isProfit'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $accounts = Account::query()
            ->whereIn('accounts.type', ['REVENUE', 'EXPENSE'])
            ->whereDoesntHave('children')
            ->leftJoin('journal_lines as jl', 'jl.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->select([
                'accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance',
                DB::raw("COALESCE(SUM(CASE WHEN je.id IS NOT NULL AND je.is_voided = false AND je.trx_date >= ? AND je.trx_date <= ? AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS period_debit"),
                DB::raw("COALESCE(SUM(CASE WHEN je.id IS NOT NULL AND je.is_voided = false AND je.trx_date >= ? AND je.trx_date <= ? AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS period_credit"),
            ])
            ->addBinding([$dateFrom, $dateTo, $dateFrom, $dateTo], 'select')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($r) {
                $r->amount = $r->normal_balance === 'C'
                    ? (int)$r->period_credit - (int)$r->period_debit
                    : (int)$r->period_debit  - (int)$r->period_credit;
                return $r;
            });

        $revenues     = $accounts->where('type', 'REVENUE')->values();
        $expenses     = $accounts->where('type', 'EXPENSE')->values();
        $totalRevenue = (int) $revenues->sum('amount');
        $totalExpense = (int) $expenses->sum('amount');
        $netIncome    = $totalRevenue - $totalExpense;

        $filename = 'laba-rugi_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($revenues, $expenses, $totalRevenue, $totalExpense, $netIncome, $dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Laporan Laba Rugi', 'Per: ' . $dateFrom . ' s/d ' . $dateTo], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Kode', 'Nama Akun', 'Jumlah (Rp)'], ';');
            fputcsv($out, ['', '-- PENDAPATAN --'], ';');
            foreach ($revenues as $r) {
                fputcsv($out, [$r->code, $r->name, $r->amount], ';');
            }
            fputcsv($out, ['', 'Total Pendapatan', $totalRevenue], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['', '-- BEBAN --'], ';');
            foreach ($expenses as $r) {
                fputcsv($out, [$r->code, $r->name, $r->amount], ';');
            }
            fputcsv($out, ['', 'Total Beban', $totalExpense], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['', ($netIncome >= 0 ? 'Laba Bersih' : 'Rugi Bersih'), $netIncome], ';');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
