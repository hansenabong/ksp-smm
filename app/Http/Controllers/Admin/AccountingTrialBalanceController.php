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
        $dateTo   = $validated['date_to']   ?? now()->toDateString();
        $showZero = (bool) ($validated['show_zero'] ?? false);

        // Fetch four aggregates per account:
        //   ob_debit / ob_credit    = cumulative activity BEFORE date_from (opening balance)
        //   period_debit / period_credit = activity WITHIN [date_from, date_to] (period movement)
        // Ending balance = opening + net period movement.
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
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date < ?
                     AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS ob_debit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date < ?
                     AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS ob_credit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS period_debit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS period_credit"),
            ])
            ->addBinding([$dateFrom, $dateFrom, $dateFrom, $dateTo, $dateFrom, $dateTo], 'select')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get();

        // Derive display columns: place net balance into the correct D or C column.
        // Debit-normal (ASSET, EXPENSE): positive net → Debit col; negative (abnormal) → Credit col.
        // Credit-normal (LIABILITY, EQUITY, REVENUE): positive net → Credit col; negative (abnormal) → Debit col.
        $rows = $rows->map(function ($r) {
            $obD = (int) $r->ob_debit;
            $obC = (int) $r->ob_credit;
            $pd  = (int) $r->period_debit;
            $pc  = (int) $r->period_credit;

            $isDebitNormal = $r->normal_balance === 'D';

            $obNet  = $isDebitNormal ? ($obD - $obC) : ($obC - $obD);
            $endNet = $isDebitNormal ? ($obD + $pd - $obC - $pc) : ($obC + $pc - $obD - $pd);

            if ($isDebitNormal) {
                $r->ob_d_col     = $obNet  >= 0 ? $obNet  : 0;
                $r->ob_c_col     = $obNet  <  0 ? -$obNet  : 0;
                $r->ending_d_col = $endNet >= 0 ? $endNet : 0;
                $r->ending_c_col = $endNet <  0 ? -$endNet : 0;
            } else {
                $r->ob_d_col     = $obNet  <  0 ? -$obNet  : 0;
                $r->ob_c_col     = $obNet  >= 0 ? $obNet  : 0;
                $r->ending_d_col = $endNet <  0 ? -$endNet : 0;
                $r->ending_c_col = $endNet >= 0 ? $endNet : 0;
            }

            $r->ob_debit      = $obD;
            $r->ob_credit     = $obC;
            $r->period_debit  = $pd;
            $r->period_credit = $pc;
            $r->is_abnormal   = $endNet < 0;

            return $r;
        });

        if (!$showZero) {
            $rows = $rows->filter(
                fn($r) => $r->ob_debit > 0 || $r->ob_credit > 0
                       || $r->period_debit > 0 || $r->period_credit > 0
            )->values();
        }

        // Grand totals
        $totalObD     = (int) $rows->sum('ob_d_col');
        $totalObC     = (int) $rows->sum('ob_c_col');
        $totalPeriodD = (int) $rows->sum('period_debit');
        $totalPeriodC = (int) $rows->sum('period_credit');
        $totalEndingD = (int) $rows->sum('ending_d_col');
        $totalEndingC = (int) $rows->sum('ending_c_col');

        // A correctly posted ledger must satisfy D = C in every column pair.
        $obBalanced     = $totalObD === $totalObC;
        $periodBalanced = $totalPeriodD === $totalPeriodC;
        $endingBalanced = $totalEndingD === $totalEndingC;
        $isBalanced     = $periodBalanced && $endingBalanced;

        // Group rows by type in standard accounting order
        $typeOrder  = ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'];
        $typeLabels = [
            'ASSET'     => 'Aktiva',
            'LIABILITY' => 'Kewajiban',
            'EQUITY'    => 'Modal',
            'REVENUE'   => 'Pendapatan',
            'EXPENSE'   => 'Beban',
        ];
        $groupedRows = collect($typeOrder)
            ->mapWithKeys(fn($type) => [$type => $rows->where('type', $type)->values()])
            ->filter(fn($group) => $group->isNotEmpty());

        return view('admin.accounting.trial_balance.index', compact(
            'dateFrom', 'dateTo', 'showZero',
            'groupedRows', 'typeLabels',
            'totalObD', 'totalObC',
            'totalPeriodD', 'totalPeriodC',
            'totalEndingD', 'totalEndingC',
            'obBalanced', 'periodBalanced', 'endingBalanced', 'isBalanced'
        ));
    }
}
