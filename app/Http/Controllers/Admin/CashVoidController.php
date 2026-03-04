<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\LoanPayment;
use App\Models\Loan;
use App\Models\SavingTransaction;
use App\Services\Accounting\AutoPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashVoidController extends Controller
{
    public function create(CashTransaction $tx)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        // load relasi untuk display di view
        $tx->load(['fromCashAccount', 'toCashAccount', 'creator']);

        return view('admin.cash.void', compact('tx'));
    }

    public function store(Request $request, CashTransaction $tx, AutoPostingService $autoPosting)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($tx, $validated, $autoPosting) {

            $tx = CashTransaction::lockForUpdate()->findOrFail($tx->id);

            if ((bool) $tx->is_voided) {
                throw ValidationException::withMessages(['void' => 'Transaksi kas sudah di-VOID.']);
            }

            // cek apakah cash tx ini berasal dari modul lain
            $linkedSaving = SavingTransaction::where('cash_transaction_id', $tx->id)->exists();
            $linkedLoanPayment = LoanPayment::where('cash_transaction_id', $tx->id)->exists();
            $linkedLoanDisbursement = Loan::where('disbursed_cash_transaction_id', $tx->id)->exists();

            if ($linkedSaving || $linkedLoanPayment || $linkedLoanDisbursement) {
                throw ValidationException::withMessages([
                    'void' => 'Transaksi ini berasal dari Simpanan/Pinjaman. VOID harus dilakukan dari modul asal agar konsisten.'
                ]);
            }

            // void cash tx
            $tx->forceFill([
                'is_voided' => true,
                'void_reason' => $validated['void_reason'],
                'voided_by' => auth()->id(),
                'voided_at' => now(),
            ])->save();

            // void jurnal cash tx
            $autoPosting->voidBySource('cash_transactions', $tx->id, $validated['void_reason']);
        });

        $redirectRoute = match ($tx->type) {
            'INCOME' => 'admin.cash.income.index',
            'EXPENSE' => 'admin.cash.expense.index',
            default => 'admin.cash.transfer.index',
        };

        $returnUrl = $request->input('return');

        // only allow internal URLs to prevent open redirect
        if ($returnUrl && !str_starts_with($returnUrl, url('/'))) {
            $returnUrl = null;
        }

        return redirect($returnUrl ?: route($redirectRoute))
            ->with('success', 'Transaksi kas berhasil di-VOID.');
    }
}
