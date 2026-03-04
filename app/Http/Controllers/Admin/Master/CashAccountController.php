<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashAccountController extends Controller
{
    private array $types = ['CASH', 'BANK'];

    public function index(Request $request)
    {
        $q = CashAccount::query()->with('ledgerAccount')->orderBy('name');

        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        if ($request->filled('type')) {
            $q->where('type', $request->type);
        }

        if ($request->filled('q')) {
            $q->where('name', 'ilike', '%'.$request->q.'%');
        }

        $cashAccounts = $q->paginate(15)->withQueryString();

        return view('admin.master.cash_accounts.index', [
            'cashAccounts' => $cashAccounts,
            'types'        => $this->types,
        ]);
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('admin.master.cash_accounts.create', [
            'types'    => $this->types,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120'],
            'type'              => ['required', Rule::in($this->types)],
            'is_active'         => ['nullable', 'boolean'],
            'ledger_account_id' => ['required', 'exists:accounts,id'],
        ]);

        CashAccount::create([
            'name'              => $data['name'],
            'type'              => $data['type'],
            'is_active'         => (bool)($data['is_active'] ?? true),
            'ledger_account_id' => $data['ledger_account_id'],
        ]);

        return redirect()->route('admin.master.cash_accounts.index')
            ->with('success', 'Kas berhasil ditambahkan.');
    }

    public function edit(CashAccount $cashAccount)
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('admin.master.cash_accounts.edit', [
            'cashAccount' => $cashAccount,
            'types'       => $this->types,
            'accounts'    => $accounts,
        ]);
    }

    public function update(Request $request, CashAccount $cashAccount)
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:120'],
            'type'              => ['required', Rule::in($this->types)],
            'is_active'         => ['nullable', 'boolean'],
            'ledger_account_id' => ['required', 'exists:accounts,id'],
        ]);

        $cashAccount->update([
            'name'              => $data['name'],
            'type'              => $data['type'],
            'is_active'         => (bool)($data['is_active'] ?? $cashAccount->is_active),
            'ledger_account_id' => $data['ledger_account_id'],
        ]);

        return redirect()->route('admin.master.cash_accounts.index')
            ->with('success', 'Kas berhasil diupdate.');
    }

    public function toggle(CashAccount $cashAccount)
    {
        $cashAccount->update(['is_active' => ! $cashAccount->is_active]);

        return redirect()->back()->with('success', 'Status kas berhasil diubah.');
    }
}
