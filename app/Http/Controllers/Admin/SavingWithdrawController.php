<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;
use App\Services\Savings\SavingsService;
use Illuminate\Http\Request;

class SavingWithdrawController extends Controller
{
    public function index(Request $request)
    {
        $q = SavingTransaction::query()
            ->notVoided()
            ->where('type', 'WITHDRAW')
            ->with(['member', 'savingProduct', 'cashAccount', 'creator']);

        if ($request->filled('date_from')) {
            $q->whereDate('trx_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('trx_date', '<=', $request->date_to);
        }
        if ($request->filled('member_id')) {
            $q->where('member_id', $request->member_id);
        }
        if ($request->filled('saving_product_id')) {
            $q->where('saving_product_id', $request->saving_product_id);
        }
        if ($request->filled('cash_account_id')) {
            $q->where('cash_account_id', $request->cash_account_id);
        }

        $transactions = $q->orderByDesc('trx_date')->paginate(10)->withQueryString();

        $members = Member::orderBy('name')->get();
        $savingProducts = SavingProduct::where('is_active', true)->orderBy('name')->get();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.savings.withdraw.index', compact(
            'transactions',
            'members',
            'savingProducts',
            'cashAccounts'
        ));
    }

    public function create()
    {
        $members = Member::orderBy('name')->get();
        $savingProducts = SavingProduct::active()
            ->where('is_withdrawable', true)
            ->orderBy('name')
            ->get();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.savings.withdraw.create', compact('members', 'savingProducts', 'cashAccounts'));
    }

    public function store(Request $request, SavingsService $service)
    {
        $validated = $request->validate([
            'trx_date' => ['required', 'date'],
            'member_id' => ['required', 'exists:members,id'],
            'saving_product_id' => ['required', 'exists:saving_products,id'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'], // kas sumber
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $service->withdraw($validated, auth()->id());

        return redirect()
            ->route('admin.savings.withdraw.index')
            ->with('success', 'Penarikan simpanan berhasil disimpan.');
    }
}
