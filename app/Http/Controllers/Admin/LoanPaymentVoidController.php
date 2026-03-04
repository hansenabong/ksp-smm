<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use App\Services\Loans\LoanService;
use Illuminate\Http\Request;

class LoanPaymentVoidController extends Controller
{
    public function create(LoanPayment $payment)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $payment->load(['loan.member', 'cashAccount']);
        return view('admin.loans.payments.void', compact('payment'));
    }

    public function store(Request $request, LoanPayment $payment, LoanService $service)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $service->voidPayment($payment->id, $validated['void_reason'], auth()->id());

        $returnUrl = $request->input('return');

        if ($returnUrl && !str_starts_with($returnUrl, url('/'))) {
            $returnUrl = null;
        }

        $fallback = route('admin.loans.active.show', $payment->loan_id);

        return redirect($returnUrl ?: $fallback)
            ->with('success', 'Pembayaran berhasil di-VOID.');
    }
}
