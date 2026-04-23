<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\Member;
use App\Models\SavingTransaction;
use App\Services\Cash\CashTransactionService;

class AdminHomeController extends Controller
{
    public function __construct(private CashTransactionService $cashService) {}

    public function index()
    {
        // Members
        $totalActiveMembers = Member::active()->count();

        // Cash balances per account + grand total
        // DEPOSITO accounts are view-only and excluded from Total Saldo Kas.
        $cashBalances     = collect($this->cashService->getAllBalances());
        $totalCashBalance = (int) $cashBalances->where('type', '!=', 'DEPOSITO')->sum('balance');

        // Total savings balance across all products and members (DEPOSIT − WITHDRAWAL)
        $totalSavings = (int) SavingTransaction::notVoided()
            ->selectRaw("SUM(CASE WHEN type = 'DEPOSIT' THEN amount ELSE -amount END) AS net")
            ->value('net');

        // Active loans
        $activeLoanCount  = Loan::where('status', 'ACTIVE')->count();
        $totalOutstanding = (int) Loan::where('status', 'ACTIVE')->sum('outstanding');

        // Pending applications
        $pendingApplicationsCount = LoanApplication::where('status', 'PENDING')->count();
        $pendingApplications      = LoanApplication::where('status', 'PENDING')
            ->with(['member', 'loanProduct'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Current-month cash income & expense
        $monthStart   = now()->startOfMonth()->toDateString();
        $today        = now()->toDateString();
        $currentMonth = now()->translatedFormat('F Y');

        $monthIncome  = (int) CashTransaction::notVoided()
            ->where('type', 'INCOME')
            ->whereBetween('trx_date', [$monthStart, $today])
            ->sum('amount');

        $monthExpense = (int) CashTransaction::notVoided()
            ->where('type', 'EXPENSE')
            ->whereBetween('trx_date', [$monthStart, $today])
            ->sum('amount');

        // Recent cash transactions (latest 8)
        $recentTransactions = CashTransaction::notVoided()
            ->with(['fromCashAccount', 'toCashAccount'])
            ->orderByDesc('trx_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin.home', compact(
            'totalActiveMembers',
            'cashBalances', 'totalCashBalance',
            'totalSavings',
            'activeLoanCount', 'totalOutstanding',
            'pendingApplicationsCount', 'pendingApplications',
            'monthIncome', 'monthExpense', 'currentMonth',
            'recentTransactions'
        ));
    }
}
