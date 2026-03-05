<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\SavingProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavingProductController extends Controller
{
    public function index(Request $request)
    {
        $q = SavingProduct::query()->with('ledgerAccount')->orderBy('name');

        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        if ($request->filled('q')) {
            $q->where('name', 'ilike', '%' . $request->q . '%');
        }

        $savingProducts = $q->paginate(15)->withQueryString();

        return view('admin.master.saving_products.index', compact('savingProducts'));
    }

    public function create()
    {
        $liabilityAccounts = Account::byType('LIABILITY')->leaf()->active()->orderBy('code')->get();

        return view('admin.master.saving_products.create', compact('liabilityAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:120', 'unique:saving_products,name'],
            'is_active'          => ['nullable', 'boolean'],
            'is_withdrawable'    => ['nullable', 'boolean'],
            'ledger_account_id'  => ['nullable', 'exists:accounts,id'],
        ]);

        SavingProduct::create([
            'name'              => $data['name'],
            'is_active'         => $request->boolean('is_active'),
            'is_withdrawable'   => $request->boolean('is_withdrawable'),
            'ledger_account_id' => $data['ledger_account_id'] ?? null,
        ]);

        return redirect()->route('admin.master.saving_products.index')
            ->with('success', 'Jenis simpanan berhasil ditambahkan.');
    }

    public function edit(SavingProduct $savingProduct)
    {
        $liabilityAccounts = Account::byType('LIABILITY')->leaf()->active()->orderBy('code')->get();

        return view('admin.master.saving_products.edit', compact('savingProduct', 'liabilityAccounts'));
    }

    public function update(Request $request, SavingProduct $savingProduct)
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:120', Rule::unique('saving_products', 'name')->ignore($savingProduct->id)],
            'is_active'          => ['nullable', 'boolean'],
            'is_withdrawable'    => ['nullable', 'boolean'],
            'ledger_account_id'  => ['nullable', 'exists:accounts,id'],
        ]);

        $savingProduct->update([
            'name'              => $data['name'],
            'is_active'         => $request->boolean('is_active'),
            'is_withdrawable'   => $request->boolean('is_withdrawable'),
            'ledger_account_id' => $data['ledger_account_id'] ?? $savingProduct->ledger_account_id,
        ]);

        return redirect()->route('admin.master.saving_products.index')
            ->with('success', 'Jenis simpanan berhasil diupdate.');
    }

    public function toggle(SavingProduct $savingProduct)
    {
        $savingProduct->update(['is_active' => ! $savingProduct->is_active]);

        return redirect()->back()->with('success', 'Status jenis simpanan berhasil diubah.');
    }
}
