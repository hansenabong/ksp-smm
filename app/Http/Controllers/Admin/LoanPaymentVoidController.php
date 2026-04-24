<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use App\Services\Loans\LoanService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class LoanPaymentVoidController extends Controller
{
    public function create(LoanPayment $payment)
    {
        $payment->load(['loan.member', 'cashAccount']);
        return view('admin.loans.payments.void', compact('payment'));
    }

    public function store(Request $request, LoanPayment $payment, LoanService $service, AuditLogService $audit)
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $service->voidPayment($payment->id, $validated['void_reason'], auth()->id());

        $audit->record('void', 'LoanPayment', $payment->id,
            "VOID angsuran pinjaman #{$payment->loan_id} anggota [{$payment->loan?->member?->member_no}] — {$validated['void_reason']}",
            [
                'loan_id' => $payment->loan_id,
                'amount'  => (int) $payment->amount,
                'member'  => $payment->loan?->member?->member_no,
                'reason'  => $validated['void_reason'],
            ]
        );

        // Accept only same-host URLs or relative paths — never a bare external URL.
        $returnUrl = (string) $request->input('return', '');
        $appHost   = parse_url((string) config('app.url'), PHP_URL_HOST);
        $inputHost = parse_url($returnUrl, PHP_URL_HOST);
        $isSafe = $returnUrl !== '' && (
            $inputHost === null && str_starts_with($returnUrl, '/')
            || ($appHost && $inputHost === $appHost)
        );

        $fallback = route('admin.loans.active.show', $payment->loan_id);

        return redirect($isSafe ? $returnUrl : $fallback)
            ->with('success', 'Pembayaran berhasil di-VOID.');
    }
}
