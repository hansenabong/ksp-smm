<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashCategory;
use App\Models\DepositoAccount;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Services\Savings\SavingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepositoController extends Controller
{
    public function index(Request $request)
    {
        $q = DepositoAccount::query()->with(['member', 'savingProduct']);

        if ($request->filled('status')) {
            // Pseudo-status "MATURED" filter = ACTIVE + maturity_date <= today
            if ($request->status === 'MATURED') {
                $q->where('status', DepositoAccount::STATUS_ACTIVE)
                    ->whereDate('maturity_date', '<=', now()->toDateString());
            } else {
                $q->where('status', $request->status);
            }
        }

        if ($request->filled('member_id')) {
            $q->where('member_id', $request->member_id);
        }

        if ($request->filled('saving_product_id')) {
            $q->where('saving_product_id', $request->saving_product_id);
        }

        $depositos = $q->orderByDesc('start_date')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        $members = Member::orderBy('name')->get();
        $depositoProducts = SavingProduct::active()->where('is_deposito', true)->orderBy('name')->get();

        return view('admin.deposito.index', compact('depositos', 'members', 'depositoProducts'));
    }

    public function create()
    {
        $members = Member::active()->orderBy('member_no')->get();
        $depositoProducts = SavingProduct::active()
            ->where('is_deposito', true)
            ->orderBy('name')
            ->get();
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();

        return view('admin.deposito.create', compact('members', 'depositoProducts', 'cashAccounts'));
    }

    public function store(Request $request, SavingsService $service)
    {
        $isAnggota = $request->boolean('is_anggota', true);

        $validated = $request->validate([
            'trx_date'         => ['required', 'date'],
            'is_anggota'       => ['boolean'],
            'member_id'        => $isAnggota
                ? ['required', Rule::exists('members', 'id')->where(fn ($q) => $q->where('is_active', true))]
                : ['nullable'],
            'depositor_name'   => $isAnggota ? ['nullable'] : ['required', 'string', 'max:100'],
            'depositor_phone'  => ['nullable', 'string', 'max:20'],
            'depositor_ktp'    => ['nullable', 'string', 'max:20'],
            'saving_product_id' => [
                'required',
                Rule::exists('saving_products', 'id')->where(fn ($q) => $q->where('is_deposito', true)->where('is_active', true)),
            ],
            'cash_account_id'  => ['required', 'exists:cash_accounts,id'],
            'amount'           => ['required', 'integer', 'min:1'],
            'description'      => ['nullable', 'string', 'max:255'],
            'bank_account_name'=> ['nullable', 'string', 'max:100'],
            'bank_account_no'  => ['nullable', 'string', 'max:50'],
            'bank_name'        => ['nullable', 'string', 'max:100'],
        ]);

        if (! $isAnggota) {
            $validated['member_id'] = null;
        }

        $tx = $service->deposit($validated, auth()->id());

        return redirect()
            ->route('admin.deposito.show', $tx->deposito_account_id)
            ->with('success', 'Kontrak deposito berhasil dibuka.');
    }

    public function show(DepositoAccount $deposito)
    {
        $deposito->load([
            'member',
            'savingProduct',
            'creator',
            'transactions' => fn ($q) => $q->orderBy('trx_date')->orderBy('id'),
            'transactions.cashAccount',
        ]);

        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $expenseCategories = CashCategory::active()->forType('EXPENSE')->orderBy('name')->get();

        return view('admin.deposito.show', compact('deposito', 'cashAccounts', 'expenseCategories'));
    }

    public function edit(DepositoAccount $deposito)
    {
        return view('admin.deposito.edit', compact('deposito'));
    }

    public function update(Request $request, DepositoAccount $deposito)
    {
        $validated = $request->validate([
            'bank_account_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no'   => ['nullable', 'string', 'max:50'],
            'bank_name'         => ['nullable', 'string', 'max:100'],
        ]);

        $deposito->update($validated);

        return redirect()
            ->route('admin.deposito.show', $deposito)
            ->with('success', 'Data rekening bank deposito berhasil diperbarui.');
    }

    public function printIndex(Request $request)
    {
        $q = DepositoAccount::query()->with(['member', 'savingProduct']);

        if ($request->filled('status')) {
            if ($request->status === 'MATURED') {
                $q->where('status', DepositoAccount::STATUS_ACTIVE)
                    ->whereDate('maturity_date', '<=', now()->toDateString());
            } else {
                $q->where('status', $request->status);
            }
        }

        if ($request->filled('member_id')) {
            $q->where('member_id', $request->member_id);
        }

        if ($request->filled('saving_product_id')) {
            $q->where('saving_product_id', $request->saving_product_id);
        }

        $depositos = $q->orderByDesc('start_date')->orderByDesc('id')->get();

        $filterMember = $request->filled('member_id')
            ? Member::find($request->member_id)?->name
            : null;
        $filterProduct = $request->filled('saving_product_id')
            ? SavingProduct::find($request->saving_product_id)?->name
            : null;
        $filterStatus = $request->input('status');

        $pdf = Pdf::loadView('admin.deposito.print-index', compact(
            'depositos', 'filterMember', 'filterProduct', 'filterStatus'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('deposito-' . now()->format('Y-m-d') . '.pdf');
    }

    public function printShow(DepositoAccount $deposito)
    {
        $deposito->load(['member', 'savingProduct', 'creator']);

        $pdf = Pdf::loadView('admin.deposito.print-show', compact('deposito'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("deposito-{$deposito->reference_code}.pdf");
    }

    public function cair(Request $request, DepositoAccount $deposito, SavingsService $service)
    {
        $validated = $request->validate([
            'trx_date' => ['required', 'date'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'interest_cash_category_id' => ['nullable', 'exists:cash_categories,id'],
        ]);

        $service->cair([
            'deposito_account_id' => $deposito->id,
            'trx_date' => $validated['trx_date'],
            'cash_account_id' => $validated['cash_account_id'],
            'interest_cash_category_id' => $validated['interest_cash_category_id'] ?? null,
        ], auth()->id());

        return redirect()
            ->route('admin.deposito.show', $deposito)
            ->with('success', "Deposito {$deposito->reference_code} berhasil dicairkan.");
    }
}
