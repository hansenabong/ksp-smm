<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;
use App\Models\Loan;
use App\Models\LoanPayment;

class MemberPortalController extends Controller
{
    /** Resolve the member linked to the logged-in user, or redirect. */
    private function getMember()
    {
        $member = Member::where('user_id', auth()->id())->first();
        if (! $member) {
            return redirect()->route('portal.not-linked');
        }
        return $member;
    }

    public function notLinked()
    {
        return view('portal.not-linked');
    }

    public function dashboard()
    {
        $member = $this->getMember();
        if ($member instanceof \Illuminate\Http\RedirectResponse) return $member;

        $products = SavingProduct::active()->get();

        $savingsBalances = $products->map(function ($p) use ($member) {
            $deposit  = SavingTransaction::notVoided()
                ->where('member_id', $member->id)
                ->where('saving_product_id', $p->id)
                ->where('type', 'DEPOSIT')
                ->sum('amount');
            $withdraw = SavingTransaction::notVoided()
                ->where('member_id', $member->id)
                ->where('saving_product_id', $p->id)
                ->where('type', 'WITHDRAW')
                ->sum('amount');
            return [
                'product' => $p,
                'balance' => (int)$deposit - (int)$withdraw,
            ];
        })->filter(fn($r) => $r['balance'] > 0);

        $totalSavings = $savingsBalances->sum('balance');

        $activeLoans = Loan::where('member_id', $member->id)
            ->where('status', 'ACTIVE')
            ->with('loanProduct')
            ->get();

        $totalOutstanding = $activeLoans->sum('outstanding');

        return view('portal.dashboard', compact('member', 'savingsBalances', 'totalSavings', 'activeLoans', 'totalOutstanding'));
    }

    public function savings()
    {
        $member = $this->getMember();
        if ($member instanceof \Illuminate\Http\RedirectResponse) return $member;

        $products = SavingProduct::active()->get();

        $byProduct = $products->map(function ($p) use ($member) {
            $transactions = SavingTransaction::notVoided()
                ->where('member_id', $member->id)
                ->where('saving_product_id', $p->id)
                ->orderByDesc('trx_date')
                ->orderByDesc('id')
                ->get();

            $deposit  = $transactions->where('type', 'DEPOSIT')->sum('amount');
            $withdraw = $transactions->where('type', 'WITHDRAW')->sum('amount');

            return [
                'product'      => $p,
                'balance'      => (int)$deposit - (int)$withdraw,
                'transactions' => $transactions,
            ];
        })->filter(fn($r) => $r['transactions']->isNotEmpty());

        return view('portal.savings', compact('member', 'byProduct'));
    }

    public function loans()
    {
        $member = $this->getMember();
        if ($member instanceof \Illuminate\Http\RedirectResponse) return $member;

        $loans = Loan::where('member_id', $member->id)
            ->with(['loanProduct', 'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')])
            ->orderByDesc('disbursed_date')
            ->get();

        return view('portal.loans', compact('member', 'loans'));
    }
}
