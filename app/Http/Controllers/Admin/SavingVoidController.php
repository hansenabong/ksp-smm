<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingTransaction;
use App\Services\Savings\SavingsService;
use Illuminate\Http\Request;

class SavingVoidController extends Controller
{
    public function create(SavingTransaction $transaction)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $transaction->load(['member', 'savingProduct', 'cashAccount']);
        return view('admin.savings.void', compact('transaction'));
    }

    public function store(Request $request, SavingTransaction $transaction, SavingsService $service)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $service->voidTransaction($transaction->id, $validated['void_reason'], auth()->id());

        $returnUrl = $request->input('return');

        // safety: hanya allow URL internal aplikasi
        if ($returnUrl && !str_starts_with($returnUrl, url('/'))) {
            $returnUrl = null;
        }
        // fallback kalau tidak ada return
        $fallback = $transaction->type === 'DEPOSIT'
            ? route('admin.savings.deposit.index')
            : route('admin.savings.withdraw.index');

        return redirect($returnUrl ?: $fallback)
            ->with('success', 'Transaksi simpanan berhasil di-VOID.');
    }
}
