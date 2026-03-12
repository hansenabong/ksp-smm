<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Services\Cash\CashTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function printAll(Request $request)
    {
        $q = CashTransaction::query()
            ->notVoided()
            ->where('type', 'EXPENSE')
            ->with(['fromCashAccount', 'cashCategory', 'creator']);

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

        $transactions = $q->orderBy('trx_date')->orderBy('id')->get();

        $filterAccount  = $request->filled('from_cash_account_id')
            ? CashAccount::find($request->from_cash_account_id)?->name
            : null;
        $filterCategory = $request->filled('cash_category_id')
            ? CashCategory::find($request->cash_category_id)?->name
            : null;

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $pdf = Pdf::loadView('admin.cash.expense.print-all', compact(
            'transactions', 'dateFrom', 'dateTo', 'filterAccount', 'filterCategory'
        ))->setPaper('a4', 'portrait');

        $suffix = ($dateFrom && $dateTo) ? "{$dateFrom}_{$dateTo}" : now()->format('Y-m-d');
        return $pdf->download("pengeluaran-kas-{$suffix}.pdf");
    }

    public function create()
    {
        $cashAccounts   = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories     = CashCategory::active()->forType('EXPENSE')
            ->orderBy('sort_order')->orderBy('name')->get();
        $accountTypeMap = $cashAccounts->pluck('type', 'id');

        return view('admin.cash.expense.create', compact('cashAccounts', 'categories', 'accountTypeMap'));
    }

    public function store(Request $request, CashTransactionService $service)
    {
        $accountType = CashAccount::find($request->from_cash_account_id)?->type;
        $refRequired = in_array($accountType, ['CHECK', 'GIRO']) ? 'required' : 'nullable';

        $validated = $request->validate([
            'trx_date'             => ['required', 'date'],
            'from_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'cash_category_id'     => ['required', 'exists:cash_categories,id'],
            'amount'               => ['required', 'integer', 'min:1'],
            'description'          => ['nullable', 'string', 'max:255'],
            'reference_code'       => [$refRequired, 'string', 'max:100'],
        ]);

        $cashCategory = CashCategory::findOrFail($validated['cash_category_id']);
        $validated['category'] = $cashCategory->name;

        $tx = $service->createExpense($validated, auth()->id());

        return redirect()
            ->route('admin.cash.transactions.show', $tx)
            ->with('success', 'Pengeluaran berhasil disimpan.');
    }
}
