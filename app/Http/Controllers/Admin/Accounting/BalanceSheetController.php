<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'date_to' => ['nullable', 'date'],
        ]);

        $dateTo = $validated['date_to'] ?? now()->toDateString();

        // Fetch cumulative debit/credit (from inception to date_to) for ALL leaf accounts.
        // ASSET, LIABILITY, EQUITY → balance sheet items.
        // REVENUE, EXPENSE → used to compute cumulative net income for equity section.
        $allAccounts = Account::query()
            ->where('accounts.is_active', true)
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
                     AND je.trx_date <= ?
                     AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS total_debit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date <= ?
                     AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS total_credit"),
            ])
            ->addBinding([$dateTo, $dateTo], 'select')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($r) {
                $r->total_debit  = (int) $r->total_debit;
                $r->total_credit = (int) $r->total_credit;
                // Compute net balance in the account's normal direction
                $r->amount = $r->normal_balance === 'D'
                    ? $r->total_debit  - $r->total_credit
                    : $r->total_credit - $r->total_debit;
                return $r;
            });

        // Balance sheet groups — only show accounts with activity
        $assets      = $allAccounts->where('type', 'ASSET')->filter(fn($r) => $r->amount != 0 || $r->total_debit > 0)->values();
        $liabilities = $allAccounts->where('type', 'LIABILITY')->filter(fn($r) => $r->amount != 0 || $r->total_credit > 0)->values();
        $equities    = $allAccounts->where('type', 'EQUITY')->filter(fn($r) => $r->amount != 0)->values();

        // Cumulative net income = all revenues − all expenses up to date_to.
        // This flows into the equity section (replaces closing entries this system doesn't perform).
        $totalRevenue       = (int) $allAccounts->where('type', 'REVENUE')->sum('amount');
        $totalExpense       = (int) $allAccounts->where('type', 'EXPENSE')->sum('amount');
        $cumulativeNetIncome = $totalRevenue - $totalExpense;

        $totalAssets           = (int) $assets->sum('amount');
        $totalLiabilities      = (int) $liabilities->sum('amount');
        $totalEquity           = (int) $equities->sum('amount');
        $totalEquitySection    = $totalEquity + $cumulativeNetIncome;
        $totalLiabilityEquity  = $totalLiabilities + $totalEquitySection;

        // The fundamental accounting equation must hold: Assets = Liabilities + Equity
        $isBalanced = $totalAssets === $totalLiabilityEquity;
        $difference = $totalAssets - $totalLiabilityEquity;

        return view('admin.accounting.balance_sheet.index', compact(
            'dateTo',
            'assets', 'liabilities', 'equities',
            'totalAssets', 'totalLiabilities', 'totalEquity',
            'cumulativeNetIncome', 'totalEquitySection', 'totalLiabilityEquity',
            'isBalanced', 'difference'
        ));
    }
}
