<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Services\Cash\CashTransactionService;
use Illuminate\Http\Request;

class CashIncomeController extends Controller
{
    private array $categories = [
        'Modal/Saldo Awal',
        'Pendapatan Jasa',
        'Denda',
        'Pendapatan Lainnya',
        'Lainnya',
    ];
    public function index(Request $request)
    {
        $q = CashTransaction::query()
            ->notVoided()
            ->where('type', 'INCOME')
            ->with(['toCashAccount', 'creator']);

        if ($request->filled('date_from')) {
            $q->whereDate('trx_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('trx_date', '<=', $request->date_to);
        }
        if ($request->filled('to_cash_account_id')) {
            $q->where('to_cash_account_id', $request->to_cash_account_id);
        }
        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }

        $transactions = $q->orderByDesc('trx_date')->paginate(10)->withQueryString();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories = $this->categories;

        return view('admin.cash.income.index', compact('transactions', 'cashAccounts', 'categories'));
    }

    public function create()
    {
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories = $this->categories;
        return view('admin.cash.income.create', compact('cashAccounts', 'categories'));
    }

    public function store(Request $request, CashTransactionService $service)
    {
        $validated = $request->validate([
            'trx_date' => ['required', 'date'],
            'to_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'category' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $service->createIncome($validated, auth()->id());

        return redirect()
            ->route('admin.cash.income.index')
            ->with('success', 'Pemasukan berhasil disimpan.');
    }
}
