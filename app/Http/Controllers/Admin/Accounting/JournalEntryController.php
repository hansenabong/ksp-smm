<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JournalEntryController extends Controller
{
    private const SOURCE_TYPES = [
        'cash_transactions'  => 'Kas',
        'saving_transactions' => 'Simpanan',
        'loans'              => 'Pencairan Pinjaman',
        'loan_payments'      => 'Angsuran Pinjaman',
    ];

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $validated = $request->validate([
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date'],
            'source_type' => ['nullable', 'string'],
            'show_voided' => ['nullable', 'boolean'],
        ]);

        $dateFrom   = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo     = $validated['date_to'] ?? now()->toDateString();
        $sourceType = $validated['source_type'] ?? null;
        $showVoided = (bool) ($validated['show_voided'] ?? false);

        $query = JournalEntry::query()
            ->withSum(['lines as total_debit' => fn($q) => $q->where('dc', 'D')], 'amount')
            ->withSum(['lines as total_credit' => fn($q) => $q->where('dc', 'C')], 'amount')
            ->with('creator')
            ->whereBetween('trx_date', [$dateFrom, $dateTo]);

        if ($sourceType) {
            $query->where('source_type', $sourceType);
        }

        if (!$showVoided) {
            $query->where('is_voided', false);
        }

        $entries = $query
            ->orderByDesc('trx_date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $sourceTypes = self::SOURCE_TYPES;

        return view('admin.accounting.journal_entries.index', compact(
            'entries', 'dateFrom', 'dateTo', 'sourceType', 'showVoided', 'sourceTypes'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $dateFrom   = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->input('date_to', now()->toDateString());
        $sourceType = $request->input('source_type');
        $showVoided = (bool) $request->input('show_voided', false);

        $query = JournalEntry::query()
            ->with(['lines.account', 'creator'])
            ->whereBetween('trx_date', [$dateFrom, $dateTo]);

        if ($sourceType) { $query->where('source_type', $sourceType); }
        if (!$showVoided) { $query->where('is_voided', false); }

        $entries = $query->orderBy('trx_date')->orderBy('id')->get();

        $sourceTypes = self::SOURCE_TYPES;
        $filename = 'jurnal_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($entries, $sourceTypes, $dateFrom, $dateTo) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Jurnal Entri', 'Per: ' . $dateFrom . ' s/d ' . $dateTo], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['No JE', 'Tanggal', 'Keterangan', 'Sumber', 'Kode Akun', 'Nama Akun', 'D/K', 'Jumlah (Rp)', 'Void'], ';');
            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    fputcsv($out, [
                        $entry->id,
                        $entry->trx_date,
                        $entry->description,
                        $sourceTypes[$entry->source_type] ?? $entry->source_type,
                        $line->account?->code,
                        $line->account?->name,
                        $line->dc,
                        (int) $line->amount,
                        $entry->is_voided ? 'Ya' : '',
                    ], ';');
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function show(JournalEntry $entry)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $entry->load(['lines.account', 'creator']);

        $totalDebit  = (int) $entry->lines->sum(fn($l) => $l->dc === 'D' ? (int)$l->amount : 0);
        $totalCredit = (int) $entry->lines->sum(fn($l) => $l->dc === 'C' ? (int)$l->amount : 0);

        $sourceTypes = self::SOURCE_TYPES;

        return view('admin.accounting.journal_entries.show', compact(
            'entry', 'totalDebit', 'totalCredit', 'sourceTypes'
        ));
    }
}
