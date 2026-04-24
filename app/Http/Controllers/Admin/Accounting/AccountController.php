<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);
    }

    public function index()
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $accounts = Account::with('parent')
            ->orderBy('code')
            ->get();

        return view('admin.accounting.accounts.index', compact('accounts'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        $parents = Account::orderBy('code')->get();
        $types   = ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'];

        return view('admin.accounting.accounts.create', compact('parents', 'types'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:20', Rule::unique('accounts', 'code')],
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE'],
            'normal_balance' => ['required', 'in:D,C'],
            'parent_id'      => ['nullable', 'exists:accounts,id'],
        ]);

        Account::create([
            'code'           => $validated['code'],
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'parent_id'      => $validated['parent_id'] ?? null,
            'is_active'      => true,
        ]);

        return redirect()->route('admin.accounting.accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(Account $account)
    {
        $this->authorizeAdmin();

        // Exclude self and descendants from parent choices
        $parents = Account::where('id', '!=', $account->id)->orderBy('code')->get();
        $types   = ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'];

        return view('admin.accounting.accounts.edit', compact('account', 'parents', 'types'));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:20', Rule::unique('accounts', 'code')->ignore($account->id)],
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE'],
            'normal_balance' => ['required', 'in:D,C'],
            'parent_id'      => ['nullable', 'exists:accounts,id'],
        ]);

        if ((int)($validated['parent_id'] ?? 0) === $account->id) {
            return back()->withErrors(['parent_id' => 'Akun tidak bisa menjadi parent dirinya sendiri.'])->withInput();
        }

        $account->update([
            'code'           => $validated['code'],
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'parent_id'      => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('admin.accounting.accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function toggle(Account $account)
    {
        $this->authorizeAdmin();

        if ($account->is_active && $account->children()->where('is_active', true)->exists()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun yang masih memiliki sub-akun aktif.');
        }

        $account->update(['is_active' => !$account->is_active]);
        $status = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.accounting.accounts.index')
            ->with('success', "Akun \"{$account->name}\" berhasil {$status}.");
    }
}
