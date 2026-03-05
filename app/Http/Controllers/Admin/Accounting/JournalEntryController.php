<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Http\Request;

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
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']), 403);

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

    public function show(JournalEntry $entry)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $entry->load(['lines.account', 'creator']);

        $totalDebit  = (int) $entry->lines->sum(fn($l) => $l->dc === 'D' ? (int)$l->amount : 0);
        $totalCredit = (int) $entry->lines->sum(fn($l) => $l->dc === 'C' ? (int)$l->amount : 0);

        $sourceTypes = self::SOURCE_TYPES;

        return view('admin.accounting.journal_entries.show', compact(
            'entry', 'totalDebit', 'totalCredit', 'sourceTypes'
        ));
    }
}
