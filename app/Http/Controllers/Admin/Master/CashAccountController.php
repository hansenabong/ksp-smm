<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashAccountController extends Controller
{
    private array $types = ['CASH', 'BANK'];

    /** Parent codes and ledger prefixes per type */
    private array $parentCodes = ['CASH' => '1110', 'BANK' => '1120'];
    private array $codePrefixes = ['CASH' => '111',  'BANK' => '112'];

    /**
     * Auto-generate the next available ledger code under 1110 (CASH) or 1120 (BANK)
     * and create the Account record.
     *
     * Strategy: start from max(existing sibling code) + 1, then loop until a free
     * code is found.  The accounts.code unique index is the final guard against
     * concurrent inserts.
     */
    private function autoCreateLedger(string $type, string $name): Account
    {
        $parentCode = $this->parentCodes[$type];
        $prefix     = $this->codePrefixes[$type];

        // Lock the parent row so concurrent requests queue up here
        $parent = Account::where('code', $parentCode)->lockForUpdate()->firstOrFail();

        // Find the highest existing suffix under this parent
        $maxCode = Account::where('parent_id', $parent->id)->max('code');
        $nextNum = $maxCode ? ((int) substr($maxCode, strlen($prefix)) + 1) : 1;

        // Resolve any gaps or collisions (e.g. manually created codes)
        do {
            $code = $prefix . $nextNum++;
        } while (Account::where('code', $code)->exists());

        return Account::create([
            'code'           => $code,
            'name'           => $name,
            'type'           => 'ASSET',
            'normal_balance' => 'D',
            'parent_id'      => $parent->id,
            'is_active'      => true,
        ]);
    }

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
        return view('admin.master.cash_accounts.create', [
            'types' => $this->types,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'type'      => ['required', Rule::in($this->types)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        [$cashAccount, $ledger] = DB::transaction(function () use ($data) {
            // Auto-create the corresponding ledger account under 1110 or 1120.
            // lockForUpdate() inside autoCreateLedger() prevents concurrent duplicates.
            $ledger = $this->autoCreateLedger($data['type'], $data['name']);

            $cashAccount = CashAccount::create([
                'name'              => $data['name'],
                'type'              => $data['type'],
                'is_active'         => (bool) ($data['is_active'] ?? true),
                'ledger_account_id' => $ledger->id,
            ]);

            return [$cashAccount, $ledger];
        });

        return redirect()->route('admin.master.cash_accounts.index')
            ->with('success', 'Kas berhasil ditambahkan (akun ledger '.$ledger->code.' dibuat otomatis).');
    }

    public function edit(CashAccount $cashAccount)
    {
        return view('admin.master.cash_accounts.edit', [
            'cashAccount' => $cashAccount->load('ledgerAccount'),
            'types'       => $this->types,
        ]);
    }

    public function update(Request $request, CashAccount $cashAccount)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'type'      => ['required', Rule::in($this->types)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newLedgerCode = null;

        DB::transaction(function () use ($data, $cashAccount, &$newLedgerCode) {
            $cashAccount->refresh()->load('ledgerAccount');

            $cashAccount->update([
                'name'      => $data['name'],
                'type'      => $data['type'],
                'is_active' => (bool) ($data['is_active'] ?? $cashAccount->is_active),
            ]);

            if ($cashAccount->ledgerAccount) {
                // Sync name to existing ledger account
                $cashAccount->ledgerAccount->update(['name' => $data['name']]);
            } else {
                // Ledger was never created — auto-generate it now
                $ledger = $this->autoCreateLedger($data['type'], $data['name']);
                $cashAccount->update(['ledger_account_id' => $ledger->id]);
                $newLedgerCode = $ledger->code;
            }
        });

        $msg = $newLedgerCode
            ? 'Kas berhasil diupdate (akun ledger '.$newLedgerCode.' dibuat otomatis).'
            : 'Kas berhasil diupdate.';

        return redirect()->route('admin.master.cash_accounts.index')
            ->with('success', $msg);
    }

    public function toggle(CashAccount $cashAccount)
    {
        $cashAccount->update(['is_active' => ! $cashAccount->is_active]);

        return redirect()->back()->with('success', 'Status kas berhasil diubah.');
    }
}
