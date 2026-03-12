<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\LoanPayment;
use App\Models\Loan;
use App\Models\SavingTransaction;
use App\Services\Accounting\AutoPostingService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashVoidController extends Controller
{
    public function create(CashTransaction $tx)
    {
        $tx->load(['fromCashAccount', 'toCashAccount', 'creator']);

        return view('admin.cash.void', compact('tx'));
    }

    public function store(Request $request, CashTransaction $tx, AutoPostingService $autoPosting, AuditLogService $audit)
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($tx, $validated, $autoPosting) {

            $tx = CashTransaction::lockForUpdate()->findOrFail($tx->id);

            if ((bool) $tx->is_voided) {
                throw ValidationException::withMessages(['void_reason' => 'Transaksi kas sudah di-VOID.']);
            }

            $linkedSaving           = SavingTransaction::where('cash_transaction_id', $tx->id)->exists();
            $linkedLoanPayment      = LoanPayment::where('cash_transaction_id', $tx->id)->exists();
            $linkedLoanDisbursement = Loan::where('disbursed_cash_transaction_id', $tx->id)->exists();

            if ($linkedSaving || $linkedLoanPayment || $linkedLoanDisbursement) {
                throw ValidationException::withMessages([
                    'void_reason' => 'Transaksi ini berasal dari Simpanan/Pinjaman. VOID harus dilakukan dari modul asal agar konsisten.',
                ]);
            }

            $tx->forceFill([
                'is_voided'   => true,
                'void_reason' => $validated['void_reason'],
                'voided_by'   => auth()->id(),
                'voided_at'   => now(),
            ])->save();

            $autoPosting->voidBySource('cash_transactions', $tx->id, $validated['void_reason']);
        });

        $audit->record('void', 'CashTransaction', $tx->id,
            "VOID kas [{$tx->code}] — {$validated['void_reason']}",
            ['code' => $tx->code, 'type' => $tx->type, 'amount' => (int) $tx->amount, 'reason' => $validated['void_reason']]
        );

        $redirectRoute = match ($tx->type) {
            'INCOME'  => 'admin.cash.income.index',
            'EXPENSE' => 'admin.cash.expense.index',
            default   => 'admin.cash.transfer.index',
        };

        $returnUrl = $request->input('return');
        if ($returnUrl && !str_starts_with($returnUrl, url('/'))) {
            $returnUrl = null;
        }

        return redirect($returnUrl ?: route($redirectRoute))
            ->with('success', 'Transaksi kas berhasil di-VOID.');
    }
}
