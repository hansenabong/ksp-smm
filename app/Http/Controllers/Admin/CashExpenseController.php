<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Services\Cash\CashTransactionService;
use Illuminate\Http\Request;

class CashExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = CashTransaction::query()
            ->notVoided()
            ->where('type', 'EXPENSE')
            ->with(['fromCashAccount', 'creator']);

        if ($request->filled('date_from')) {
            $q->whereDate('trx_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('trx_date', '<=', $request->date_to);
        }
        if ($request->filled('from_cash_account_id')) {
            $q->where('from_cash_account_id', $request->from_cash_account_id);
        }
        if ($request->filled('cash_category_id')) {
            $q->where('cash_category_id', $request->cash_category_id);
        }

        $transactions = $q->orderByDesc('trx_date')->paginate(10)->withQueryString();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories   = CashCategory::active()->forType('EXPENSE')
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.cash.expense.index', compact('transactions', 'cashAccounts', 'categories'));
    }

    public function create()
    {
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories   = CashCategory::active()->forType('EXPENSE')
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.cash.expense.create', compact('cashAccounts', 'categories'));
    }

    public function store(Request $request, CashTransactionService $service)
    {
        $validated = $request->validate([
            'trx_date'             => ['required', 'date'],
            'from_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'cash_category_id'     => ['required', 'exists:cash_categories,id'],
            'amount'               => ['required', 'integer', 'min:1'],
            'description'          => ['nullable', 'string', 'max:255'],
        ]);

        $cashCategory = CashCategory::findOrFail($validated['cash_category_id']);
        $validated['category'] = $cashCategory->name;

        $service->createExpense($validated, auth()->id());

        return redirect()
            ->route('admin.cash.expense.index')
            ->with('success', 'Pengeluaran berhasil disimpan.');
    }
}
