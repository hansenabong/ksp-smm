<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $accounts = CashAccount::orderBy('name')->get();

        // transaksi aktif dalam periode
        $txQuery = CashTransaction::query()
            ->where(function ($q) {
                $q->whereNull('is_voided')->orWhere('is_voided', false);
            })
            ->whereDate('trx_date', '>=', $dateFrom)
            ->whereDate('trx_date', '<=', $dateTo);

        $transactions = (clone $txQuery)
            ->with(['fromCashAccount', 'toCashAccount', 'creator'])
            ->orderBy('trx_date')
            ->orderBy('id')
            ->get();

        // ringkasan per kas: income, expense, transfer_in, transfer_out
        $summary = [];
        foreach ($accounts as $acc) {
            $income = (clone $txQuery)->where('type', 'INCOME')->where('to_cash_account_id', $acc->id)->sum('amount');
            $expense = (clone $txQuery)->where('type', 'EXPENSE')->where('from_cash_account_id', $acc->id)->sum('amount');

            $transferIn = (clone $txQuery)->where('type', 'TRANSFER')->where('to_cash_account_id', $acc->id)->sum('amount');
            $transferOut = (clone $txQuery)->where('type', 'TRANSFER')->where('from_cash_account_id', $acc->id)->sum('amount');

            $summary[$acc->id] = [
                'income' => (int)$income,
                'expense' => (int)$expense,
                'transfer_in' => (int)$transferIn,
                'transfer_out' => (int)$transferOut,
                'net' => (int)$income - (int)$expense + (int)$transferIn - (int)$transferOut,
            ];
        }

        return view('admin.reports.cash.index', compact('dateFrom', 'dateTo', 'accounts', 'summary', 'transactions'));
    }
    public function export(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->input('date_to', now()->toDateString());

        $q = CashTransaction::query()
            ->where(function ($x) {
                $x->whereNull('is_voided')->orWhere('is_voided', false);
            })
            ->whereDate('trx_date', '>=', $dateFrom)
            ->whereDate('trx_date', '<=', $dateTo)
            ->with(['fromCashAccount', 'toCashAccount', 'creator'])
            ->orderBy('trx_date')->orderBy('id');

        $filename = "report_cash_{$dateFrom}_{$dateTo}.csv";

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM biar Excel Indonesia gak aneh
            fwrite($out, "\xEF\xBB\xBF");

            // Pakai delimiter ; biar enak di Excel locale ID
            fputcsv($out, [
                'trx_date',
                'code',
                'type',
                'from_cash',
                'to_cash',
                'category',
                'description',
                'amount',
                'user_email'
            ], ';');

            foreach ($q->cursor() as $tx) {
                $from = ($tx->type === 'EXPENSE' || $tx->type === 'TRANSFER') ? ($tx->fromCashAccount?->name ?? '') : '';
                $to   = ($tx->type === 'INCOME'  || $tx->type === 'TRANSFER') ? ($tx->toCashAccount?->name ?? '') : '';

                fputcsv($out, [
                    $tx->trx_date,
                    $tx->code,
                    $tx->type,
                    $from,
                    $to,
                    $tx->category,
                    $tx->description,
                    (int)$tx->amount,
                    $tx->creator?->email ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
