<?php

namespace App\Http\Controllers\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\SavingTransaction;
use App\Models\LoanPayment;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class VoidAuditController extends Controller
{
    public function cash(Request $request)
    {
        $transactions = $this->buildCashVoidQuery($request)->paginate(15)->withQueryString();
        return view('admin.audit.void.cash', compact('transactions'));
    }

    public function savings(Request $request)
    {
        $transactions = $this->buildSavingsVoidQuery($request)->paginate(15)->withQueryString();
        return view('admin.audit.void.savings', compact('transactions'));
    }

    public function loanPayments(Request $request)
    {
        $payments = $this->buildLoanPaymentsVoidQuery($request)->paginate(15)->withQueryString();
        return view('admin.audit.void.loan_payments', compact('payments'));
    }
    private function buildCashVoidQuery(Request $request)
    {
        $q = \App\Models\CashTransaction::query()
            ->where('is_voided', true)
            ->with(['fromCashAccount', 'toCashAccount', 'voider'])
            ->orderByDesc('voided_at');

        if ($request->filled('date_from')) $q->whereDate('voided_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('voided_at', '<=', $request->date_to);
        if ($request->filled('type')) $q->where('type', $request->type);

        return $q;
    }
    private function buildSavingsVoidQuery(Request $request)
    {
        $q = \App\Models\SavingTransaction::query()
            ->where('is_voided', true)
            ->with(['member', 'savingProduct', 'cashAccount', 'voider'])
            ->orderByDesc('voided_at');

        if ($request->filled('date_from')) $q->whereDate('voided_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('voided_at', '<=', $request->date_to);
        if ($request->filled('type')) $q->where('type', $request->type); // DEPOSIT/WITHDRAW

        return $q;
    }
    private function buildLoanPaymentsVoidQuery(Request $request)
    {
        $q = \App\Models\LoanPayment::query()
            ->where('is_voided', true)
            ->with(['loan.member', 'cashAccount', 'voider'])
            ->orderByDesc('voided_at');

        if ($request->filled('date_from')) $q->whereDate('voided_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $q->whereDate('voided_at', '<=', $request->date_to);

        return $q;
    }
    public function exportCash(Request $request)
    {
        $q = $this->buildCashVoidQuery($request);
        $filename = "audit_void_cash.csv";

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['voided_at', 'code', 'type', 'from_cash', 'to_cash', 'amount', 'reason', 'voided_by'], ';');

            foreach ($q->cursor() as $tx) {
                $from = ($tx->type === 'EXPENSE' || $tx->type === 'TRANSFER') ? ($tx->fromCashAccount?->name ?? '') : '';
                $to   = ($tx->type === 'INCOME'  || $tx->type === 'TRANSFER') ? ($tx->toCashAccount?->name ?? '') : '';

                fputcsv($out, [
                    $tx->voided_at,
                    $tx->code,
                    $tx->type,
                    $from,
                    $to,
                    (int)$tx->amount,
                    $tx->void_reason,
                    $tx->voider?->email ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    public function exportSavings(Request $request)
    {
        $q = $this->buildSavingsVoidQuery($request);
        $filename = "audit_void_savings.csv";

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['voided_at', 'trx_date', 'type', 'member_no', 'member_name', 'saving_product', 'cash', 'amount', 'reason', 'voided_by'], ';');

            foreach ($q->cursor() as $tx) {
                fputcsv($out, [
                    $tx->voided_at,
                    $tx->trx_date,
                    $tx->type,
                    $tx->member?->member_no ?? '',
                    $tx->member?->name ?? '',
                    $tx->savingProduct?->name ?? '',
                    $tx->cashAccount?->name ?? '',
                    (int)$tx->amount,
                    $tx->void_reason,
                    $tx->voider?->email ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    public function exportLoanPayments(Request $request)
    {
        $q = $this->buildLoanPaymentsVoidQuery($request);
        $filename = "audit_void_loan_payments.csv";

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['voided_at', 'loan_id', 'member_no', 'member_name', 'cash', 'amount', 'reason', 'voided_by'], ';');

            foreach ($q->cursor() as $p) {
                fputcsv($out, [
                    $p->voided_at,
                    $p->loan_id,
                    $p->loan?->member?->member_no ?? '',
                    $p->loan?->member?->name ?? '',
                    $p->cashAccount?->name ?? '',
                    (int)$p->amount,
                    $p->void_reason,
                    $p->voider?->email ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
