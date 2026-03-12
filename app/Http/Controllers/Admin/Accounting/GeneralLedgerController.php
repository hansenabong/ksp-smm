<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneralLedgerController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo   = $validated['date_to'] ?? now()->toDateString();
        $accountId = $validated['account_id'] ?? null;

        // Leaf accounts for dropdown
        $accounts = Account::active()->leaf()->orderBy('code')->get();

        $selectedAccount  = null;
        $lines            = collect();
        $openingBalance   = 0;
        $closingBalance   = 0;
        $periodDebit      = 0;
        $periodCredit     = 0;

        if ($accountId) {
            $selectedAccount = Account::findOrFail($accountId);

            // Opening balance: all non-voided lines before dateFrom
            $openingDebit = (int) JournalLine::join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_lines.account_id', $accountId)
                ->where('je.is_voided', false)
                ->where('je.trx_date', '<', $dateFrom)
                ->where('journal_lines.dc', 'D')
                ->sum('journal_lines.amount');

            $openingCredit = (int) JournalLine::join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_lines.account_id', $accountId)
                ->where('je.is_voided', false)
                ->where('je.trx_date', '<', $dateFrom)
                ->where('journal_lines.dc', 'C')
                ->sum('journal_lines.amount');

            $openingBalance = $selectedAccount->normal_balance === 'D'
                ? $openingDebit - $openingCredit
                : $openingCredit - $openingDebit;

            // Lines in period
            $rawLines = JournalLine::query()
                ->join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
                ->where('journal_lines.account_id', $accountId)
                ->where('je.is_voided', false)
                ->whereBetween('je.trx_date', [$dateFrom, $dateTo])
                ->select([
                    'journal_lines.*',
                    'je.trx_date',
                    'je.description as entry_description',
                    'je.source_type',
                    'je.source_id',
                    'je.id as journal_entry_id',
                ])
                ->orderBy('je.trx_date')
                ->orderBy('je.id')
                ->get();

            // Calculate running balance
            $running = $openingBalance;
            $isDebitNormal = $selectedAccount->normal_balance === 'D';

            $lines = $rawLines->map(function ($line) use (&$running, $isDebitNormal) {
                $debit  = $line->dc === 'D' ? (int)$line->amount : 0;
                $credit = $line->dc === 'C' ? (int)$line->amount : 0;
                $running += $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

                $line->debit_amount   = $debit;
                $line->credit_amount  = $credit;
                $line->running_balance = $running;
                return $line;
            });

            $periodDebit  = (int) $rawLines->sum(fn($l) => $l->dc === 'D' ? (int)$l->amount : 0);
            $periodCredit = (int) $rawLines->sum(fn($l) => $l->dc === 'C' ? (int)$l->amount : 0);
            $closingBalance = $running;
        }

        return view('admin.accounting.general_ledger.index', compact(
            'accounts', 'selectedAccount', 'lines',
            'openingBalance', 'closingBalance', 'periodDebit', 'periodCredit',
            'dateFrom', 'dateTo', 'accountId'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $dateFrom  = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo    = $request->input('date_to', now()->toDateString());
        $accountId = $request->input('account_id');

        abort_if(!$accountId, 422, 'Pilih akun terlebih dahulu.');

        $selectedAccount = Account::findOrFail($accountId);

        $openingDebit = (int) JournalLine::join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $accountId)->where('je.is_voided', false)
            ->where('je.trx_date', '<', $dateFrom)->where('journal_lines.dc', 'D')->sum('journal_lines.amount');
        $openingCredit = (int) JournalLine::join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $accountId)->where('je.is_voided', false)
            ->where('je.trx_date', '<', $dateFrom)->where('journal_lines.dc', 'C')->sum('journal_lines.amount');
        $openingBalance = $selectedAccount->normal_balance === 'D'
            ? $openingDebit - $openingCredit
            : $openingCredit - $openingDebit;

        $rawLines = JournalLine::query()
            ->join('journal_entries as je', 'je.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.account_id', $accountId)
            ->where('je.is_voided', false)
            ->whereBetween('je.trx_date', [$dateFrom, $dateTo])
            ->select(['journal_lines.*', 'je.trx_date', 'je.description as entry_description'])
            ->orderBy('je.trx_date')->orderBy('je.id')->get();

        $running = $openingBalance;
        $isDebitNormal = $selectedAccount->normal_balance === 'D';
        $lines = $rawLines->map(function ($line) use (&$running, $isDebitNormal) {
            $debit  = $line->dc === 'D' ? (int)$line->amount : 0;
            $credit = $line->dc === 'C' ? (int)$line->amount : 0;
            $running += $isDebitNormal ? ($debit - $credit) : ($credit - $debit);
            $line->debit_amount    = $debit;
            $line->credit_amount   = $credit;
            $line->running_balance = $running;
            return $line;
        });

        $filename = 'buku-besar_' . $selectedAccount->code . '_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($selectedAccount, $openingBalance, $lines, $dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Buku Besar', $selectedAccount->code . ' - ' . $selectedAccount->name], ';');
            fputcsv($out, ['Periode', $dateFrom . ' s/d ' . $dateTo], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Tanggal', 'Keterangan', 'Debit (Rp)', 'Kredit (Rp)', 'Saldo (Rp)'], ';');
            fputcsv($out, ['', 'Saldo Awal', '', '', $openingBalance], ';');
            foreach ($lines as $line) {
                fputcsv($out, [$line->trx_date, $line->entry_description, $line->debit_amount, $line->credit_amount, $line->running_balance], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['', 'Saldo Akhir', $lines->sum('debit_amount'), $lines->sum('credit_amount'), $lines->last()?->running_balance ?? $openingBalance], ';');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
