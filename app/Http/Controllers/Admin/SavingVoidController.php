<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingTransaction;
use App\Services\Savings\SavingsService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SavingVoidController extends Controller
{
    public function create(SavingTransaction $transaction)
    {
        $transaction->load(['member', 'savingProduct', 'cashAccount']);
        return view('admin.savings.void', compact('transaction'));
    }

    public function store(Request $request, SavingTransaction $transaction, SavingsService $service, AuditLogService $audit)
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $service->voidTransaction($transaction->id, $validated['void_reason'], auth()->id());

        $audit->record('void', 'SavingTransaction', $transaction->id,
            "VOID simpanan [{$transaction->type}] anggota [{$transaction->member?->member_no}] — {$validated['void_reason']}",
            [
                'type'   => $transaction->type,
                'amount' => (int) $transaction->amount,
                'member' => $transaction->member?->member_no,
                'reason' => $validated['void_reason'],
            ]
        );

        $returnUrl = $request->input('return');
        if ($returnUrl && !str_starts_with($returnUrl, url('/'))) {
            $returnUrl = null;
        }

        $fallback = $transaction->type === 'DEPOSIT'
            ? route('admin.savings.deposit.index')
            : route('admin.savings.withdraw.index');

        return redirect($returnUrl ?: $fallback)
            ->with('success', 'Transaksi simpanan berhasil di-VOID.');
    }
}
