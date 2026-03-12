<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashCategoryController extends Controller
{
    private function authorize(): void
    {
        abort_unless(
            auth()->user()->hasRole('SUPER_ADMIN'),
            403
        );
    }

    private function accountsForForms(): array
    {
        $pick = fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name, 'type' => $a->type];

        return [
            'incomeAccounts'  => Account::active()->leaf()
                ->whereIn('type', ['REVENUE', 'EQUITY'])
                ->orderBy('code')->get()->map($pick)->values(),
            'expenseAccounts' => Account::active()->leaf()
                ->where('type', 'EXPENSE')
                ->orderBy('code')->get()->map($pick)->values(),
        ];
    }

    private function validAccountIds(string $type): array
    {
        return match($type) {
            'INCOME'  => Account::active()->leaf()->whereIn('type', ['REVENUE', 'EQUITY'])->pluck('id')->toArray(),
            'EXPENSE' => Account::active()->leaf()->where('type', 'EXPENSE')->pluck('id')->toArray(),
            default   => [],
        };
    }

    public function index()
    {
        $this->authorize();

        $categories = CashCategory::with('account')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return view('admin.master.cash_categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize();

        ['incomeAccounts' => $incomeAccounts, 'expenseAccounts' => $expenseAccounts] = $this->accountsForForms();
        $typeOptions = ['INCOME' => 'Pemasukan', 'EXPENSE' => 'Pengeluaran', 'TRANSFER' => 'Transfer'];

        return view('admin.master.cash_categories.create', compact('incomeAccounts', 'expenseAccounts', 'typeOptions'));
    }

    public function store(Request $request)
    {
        $this->authorize();

        $type            = $request->input('type', '');
        $validAccountIds = in_array($type, ['INCOME', 'EXPENSE']) ? $this->validAccountIds($type) : [];
        // INCOME and EXPENSE must have a counterpart account — TRANSFER must not
        $accountRule = in_array($type, ['INCOME', 'EXPENSE'])
            ? ['required', Rule::in($validAccountIds)]
            : ['nullable'];

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:INCOME,EXPENSE,TRANSFER'],
            'account_id'  => $accountRule,
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ], [
            'account_id.required' => 'Akun ledger wajib dipilih untuk kategori Pemasukan / Pengeluaran.',
            'account_id.in'       => 'Akun ledger tidak valid untuk tipe kategori yang dipilih.',
        ]);

        // Unique check (name + type)
        $exists = CashCategory::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'Nama kategori sudah ada untuk tipe ini.'])->withInput();
        }

        // TRANSFER must have no account
        if ($validated['type'] === 'TRANSFER') {
            $validated['account_id'] = null;
        }

        CashCategory::create([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'account_id'  => $validated['account_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => true,
        ]);

        return redirect()
            ->route('admin.master.cash_categories.index')
            ->with('success', 'Kategori kas berhasil ditambahkan.');
    }

    public function edit(CashCategory $cashCategory)
    {
        $this->authorize();

        ['incomeAccounts' => $incomeAccounts, 'expenseAccounts' => $expenseAccounts] = $this->accountsForForms();
        $typeOptions = ['INCOME' => 'Pemasukan', 'EXPENSE' => 'Pengeluaran', 'TRANSFER' => 'Transfer'];

        return view('admin.master.cash_categories.edit', compact('cashCategory', 'incomeAccounts', 'expenseAccounts', 'typeOptions'));
    }

    public function update(Request $request, CashCategory $cashCategory)
    {
        $this->authorize();

        $type            = $request->input('type', '');
        $validAccountIds = in_array($type, ['INCOME', 'EXPENSE']) ? $this->validAccountIds($type) : [];
        $accountRule = in_array($type, ['INCOME', 'EXPENSE'])
            ? ['required', Rule::in($validAccountIds)]
            : ['nullable'];

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'type'        => ['required', 'in:INCOME,EXPENSE,TRANSFER'],
            'account_id'  => $accountRule,
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ], [
            'account_id.required' => 'Akun ledger wajib dipilih untuk kategori Pemasukan / Pengeluaran.',
            'account_id.in'       => 'Akun ledger tidak valid untuk tipe kategori yang dipilih.',
        ]);

        // Unique check (name + type), excluding self
        $exists = CashCategory::where('name', $validated['name'])
            ->where('type', $validated['type'])
            ->where('id', '!=', $cashCategory->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['name' => 'Nama kategori sudah ada untuk tipe ini.'])->withInput();
        }

        // TRANSFER must have no account
        if ($validated['type'] === 'TRANSFER') {
            $validated['account_id'] = null;
        }

        $cashCategory->update([
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'account_id'  => $validated['account_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('admin.master.cash_categories.index')
            ->with('success', 'Kategori kas berhasil diperbarui.');
    }

    public function toggle(CashCategory $cashCategory)
    {
        $this->authorize();

        $cashCategory->update(['is_active' => !$cashCategory->is_active]);

        $status = $cashCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.master.cash_categories.index')
            ->with('success', "Kategori \"{$cashCategory->name}\" berhasil {$status}.");
    }
}
