<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashCategory;
use App\Models\CashTransaction;
use App\Services\Cash\CashTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashTransferController extends Controller
{
    public function index(Request $request)
    {
        $q = CashTransaction::query()
            ->notVoided()
            ->where('type', 'TRANSFER')
            ->with(['fromCashAccount', 'toCashAccount', 'creator']);

        if ($request->filled('date_from')) {
            $q->whereDate('trx_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('trx_date', '<=', $request->date_to);
        }
        if ($request->filled('from_cash_account_id')) {
            $q->where('from_cash_account_id', $request->from_cash_account_id);
        }
        if ($request->filled('to_cash_account_id')) {
            $q->where('to_cash_account_id', $request->to_cash_account_id);
        }
        if ($request->filled('cash_category_id')) {
            $q->where('cash_category_id', $request->cash_category_id);
        }

        $transactions = $q->orderByDesc('trx_date')->paginate(10)->withQueryString();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories   = CashCategory::active()->forType('TRANSFER')
            ->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.cash.transfer.index', compact('transactions', 'cashAccounts', 'categories'));
    }

    public function printAll(Request $request)
    {
        $q = CashTransaction::query()
            ->notVoided()
            ->where('type', 'TRANSFER')
            ->with(['fromCashAccount', 'toCashAccount', 'cashCategory', 'creator']);

        if ($request->filled('date_from')) {
            $q->whereDate('trx_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('trx_date', '<=', $request->date_to);
        }
        if ($request->filled('from_cash_account_id')) {
            $q->where('from_cash_account_id', $request->from_cash_account_id);
        }
        if ($request->filled('to_cash_account_id')) {
            $q->where('to_cash_account_id', $request->to_cash_account_id);
        }
        if ($request->filled('cash_category_id')) {
            $q->where('cash_category_id', $request->cash_category_id);
        }

        $transactions = $q->orderBy('trx_date')->orderBy('id')->get();

        $filterFromAccount = $request->filled('from_cash_account_id')
            ? CashAccount::find($request->from_cash_account_id)?->name
            : null;
        $filterToAccount   = $request->filled('to_cash_account_id')
            ? CashAccount::find($request->to_cash_account_id)?->name
            : null;
        $filterCategory = $request->filled('cash_category_id')
            ? CashCategory::find($request->cash_category_id)?->name
            : null;

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $pdf = Pdf::loadView('admin.cash.transfer.print-all', compact(
            'transactions', 'dateFrom', 'dateTo', 'filterFromAccount', 'filterToAccount', 'filterCategory'
        ))->setPaper('a4', 'portrait');

        $suffix = ($dateFrom && $dateTo) ? "{$dateFrom}_{$dateTo}" : now()->format('Y-m-d');
        return $pdf->download("transfer-kas-{$suffix}.pdf");
    }

    public function create()
    {
        $cashAccounts   = CashAccount::where('is_active', true)->orderBy('name')->get();
        $categories     = CashCategory::active()->forType('TRANSFER')
            ->orderBy('sort_order')->orderBy('name')->get();
        $accountTypeMap = $cashAccounts->pluck('type', 'id');

        return view('admin.cash.transfer.create', compact('cashAccounts', 'categories', 'accountTypeMap'));
    }

    public function store(Request $request, CashTransactionService $service)
    {
        $fromType    = CashAccount::find($request->from_cash_account_id)?->type;
        $toType      = CashAccount::find($request->to_cash_account_id)?->type;
        $refRequired = (in_array($fromType, ['CHECK', 'GIRO']) || in_array($toType, ['CHECK', 'GIRO'])) ? 'required' : 'nullable';

        $validated = $request->validate([
            'trx_date'             => ['required', 'date'],
            'from_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'to_cash_account_id'   => ['required', 'exists:cash_accounts,id'],
            'cash_category_id'     => ['required', 'exists:cash_categories,id'],
            'amount'               => ['required', 'integer', 'min:1'],
            'description'          => ['nullable', 'string', 'max:255'],
            'reference_code'       => [$refRequired, 'string', 'max:100', 'unique:cash_transactions,reference_code'],
            'ref_number'           => ['nullable', 'string', 'max:150'],
        ]);

        $cashCategory = CashCategory::findOrFail($validated['cash_category_id']);
        $validated['category'] = $cashCategory->name;

        $tx = $service->createTransfer($validated, auth()->id());

        return redirect()
            ->route('admin.cash.transactions.show', $tx)
            ->with('success', 'Transfer berhasil disimpan.');
    }
}
