<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingTrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
            'show_zero' => ['nullable', 'boolean'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo   = $validated['date_to'] ?? now()->toDateString();
        $showZero = (bool) ($validated['show_zero'] ?? false);

        // Ambil debit/credit per akun dari journal_lines, hanya journal_entries yang:
        // - is_voided = false
        // - trx_date dalam range
        $rows = Account::query()
            ->where('accounts.is_active', true)
            ->leftJoin('journal_lines as jl', 'jl.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.type',
                'accounts.normal_balance',
                DB::raw("
                    COALESCE(SUM(CASE
                        WHEN je.id IS NOT NULL
                         AND je.is_voided = false
                         AND je.trx_date >= ?
                         AND je.trx_date <= ?
                         AND jl.dc = 'D'
                        THEN jl.amount ELSE 0 END), 0) AS debit
                "),
                DB::raw("
                    COALESCE(SUM(CASE
                        WHEN je.id IS NOT NULL
                         AND je.is_voided = false
                         AND je.trx_date >= ?
                         AND je.trx_date <= ?
                         AND jl.dc = 'C'
                        THEN jl.amount ELSE 0 END), 0) AS credit
                "),
            ])
            ->addBinding([$dateFrom, $dateTo, $dateFrom, $dateTo], 'select')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get();

        // hitung balance berdasarkan normal_balance
        $rows = $rows->map(function ($r) {
            $debit = (int) $r->debit;
            $credit = (int) $r->credit;

            $balance = ($r->normal_balance === 'D')
                ? ($debit - $credit)
                : ($credit - $debit);

            $r->balance = $balance;
            return $r;
        });

        if (!$showZero) {
            $rows = $rows->filter(fn($r) => ((int)$r->debit !== 0) || ((int)$r->credit !== 0) || ((int)$r->balance !== 0))
                ->values();
        }

        $totalDebit  = (int) $rows->sum(fn($r) => (int) $r->debit);
        $totalCredit = (int) $rows->sum(fn($r) => (int) $r->credit);
        $diff = $totalDebit - $totalCredit;

        return view('admin.accounting.trial_balance.index', compact(
            'dateFrom',
            'dateTo',
            'showZero',
            'rows',
            'totalDebit',
            'totalCredit',
            'diff'
        ));
    }
}
