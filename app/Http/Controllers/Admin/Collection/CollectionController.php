<?php

namespace App\Http\Controllers\Admin\Collection;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Collection\CollectionLetterController;
use App\Models\CollectionRecord;
use App\Models\CollectionLetter;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CollectionController extends Controller
{
    /**
     * Master Tagihan Nasabah — list of active loans with collection tracking.
     */
    public function index(Request $request)
    {
        $levelFilter   = $request->input('level');
        $petugasFilter = $request->input('petugas_id');
        $search        = $request->input('q');
        $sortBy        = $request->input('sort_by');

        $query = Loan::with([
                'member', 'application', 'loanProduct', 'collectionRecord.petugas',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1),
            ])
            ->where('status', 'ACTIVE')
            ->orderBy('id');

        if ($search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('member_no', 'ilike', "%{$search}%"));
        }

        if ($levelFilter !== null && $levelFilter !== '') {
            if ($levelFilter == '0') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('collectionRecord')
                      ->orWhereHas('collectionRecord', fn($r) => $r->where('escalation_level', 0));
                });
            } else {
                $query->whereHas('collectionRecord', fn($r) => $r->where('escalation_level', $levelFilter));
            }
        }

        if ($petugasFilter) {
            $query->whereHas('collectionRecord', fn($r) => $r->where('petugas_id', $petugasFilter));
        }

        $today = Carbon::today();

        if ($sortBy) {
            // Load all, enrich, sort in PHP (no pagination when sort active)
            $raw = $query->get();
            $raw->transform(fn($l) => $this->enrichLoan($l, $today));
            $loans = $this->applySort($raw, $sortBy)->values();
        } else {
            $loans = $query->paginate(100)->withQueryString();
            $loans->getCollection()->transform(fn($l) => $this->enrichLoan($l, $today));
        }

        // Summary totals (all active)
        $allActive = Loan::where('status', 'ACTIVE')
            ->with(['payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1)])
            ->get();

        $totals = [
            'count'             => $allActive->count(),
            'principal'         => $allActive->sum('principal'),
            'outstanding'       => $allActive->sum('outstanding'),
            'total_paid'        => $allActive->sum('total_paid'),
            'angsuran_berjalan' => $allActive->sum('monthly_installment'),
            'tunggakan'         => $allActive->sum(fn($l) => $this->computeArrears($l, $today)),
            'total_tagihan'     => (int) $allActive->sum(fn($l) => $this->computeTotalKewajiban($l, $today)),
            'target'            => (int) $allActive->sum(fn($l) => $this->computeTotalKewajiban($l, $today)),
            'total_realisasi'   => $allActive->sum(fn($l) => optional($l->payments->first())->amount ?? 0),
        ];

        $petugasList = User::whereHas('roles', fn($q) => $q->whereIn('name',
                ['ADMIN_COLLECTION', 'SUPER_ADMIN']))
            ->orderBy('name')->get();

        return view('admin.collection.index', compact(
            'loans', 'totals', 'petugasList', 'levelFilter', 'petugasFilter', 'search', 'today', 'sortBy'
        ));
    }

    /**
     * Export Master Tagihan Nasabah as CSV (Excel-compatible).
     */
    public function exportIndex(Request $request): StreamedResponse
    {
        $levelFilter   = $request->input('level');
        $petugasFilter = $request->input('petugas_id');
        $search        = $request->input('q');
        $sortBy        = $request->input('sort_by');

        $query = Loan::with([
                'member', 'application', 'loanProduct', 'collectionRecord.petugas',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1),
            ])
            ->where('status', 'ACTIVE')
            ->orderBy('id');

        if ($search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('member_no', 'ilike', "%{$search}%"));
        }

        if ($levelFilter !== null && $levelFilter !== '') {
            if ($levelFilter == '0') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('collectionRecord')
                      ->orWhereHas('collectionRecord', fn($r) => $r->where('escalation_level', 0));
                });
            } else {
                $query->whereHas('collectionRecord', fn($r) => $r->where('escalation_level', $levelFilter));
            }
        }

        if ($petugasFilter) {
            $query->whereHas('collectionRecord', fn($r) => $r->where('petugas_id', $petugasFilter));
        }

        $today = Carbon::today();
        $loans = $query->get();
        $loans->transform(fn($l) => $this->enrichLoan($l, $today));
        $loans = $this->applySort($loans, $sortBy)->values();

        $filename = 'master-tagihan-' . $today->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($loans) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            $headers = [
                'No', 'Nasabah', 'No. Anggota', 'Nomor Kontrak', 'Cabang',
                'Pinjaman', 'Tenor', 'Angsuran Ke', 'Tgl Jatuh Tempo', 'Angsuran Berjalan (Rp)',
                'Angsuran Bulanan (Rp)', 'Bln Telat', 'Tunggakan (Rp)',
                'Total Kewajiban (Rp)', 'Realisasi Tgl', 'Realisasi Rp', 'Realisasi Tunggakan',
                'Sisa Kewajiban (Rp)', 'Total Bayar (Rp)',
                'Alamat KTP', 'Alamat Jaminan', 'Alamat Tinggal', 'No Telepon',
                'Status Eskalasi', 'Petugas',
            ];
            fputcsv($out, $headers, ';');

            foreach ($loans as $i => $loan) {
                $rec   = $loan->collectionRecord;
                $noPk  = $loan->application?->notaris_deed_number
                      ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . date('Y', strtotime($loan->disbursed_date)));
                $level = $rec?->escalation_level ?? 0;
                $levelLabel = CollectionRecord::LEVELS[$level] ?? 'Normal';

                fputcsv($out, [
                    $i + 1,
                    strtoupper($loan->member->name),
                    $loan->member->member_no ?? '',
                    $noPk,
                    $rec?->branch ?? '',
                    $loan->principal,
                    $loan->tenor_months,
                    $loan->_ke,
                    $loan->_bejalan_tgl,
                    $loan->_angsuran_berjalan,
                    $loan->monthly_installment,
                    $loan->_bln_telat ?: '',
                    $loan->_tunggakan != 0 ? $loan->_tunggakan : '',
                    $loan->_total_kewajiban,
                    $loan->_realisasi_tgl ?? '',
                    $loan->_realisasi_rp ?: '',
                    $loan->_realisasi_tunggakan != 0 ? $loan->_realisasi_tunggakan : '',
                    $loan->_sisa_kewajiban,
                    $loan->_total_bayar,
                    $loan->member->address ?? '',
                    $loan->_alamat_jaminan,
                    $loan->_alamat_tinggal,
                    $loan->member->phone ?? '',
                    $levelLabel,
                    $rec?->petugas?->name ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Laporan Collection — collectability summary report.
     */
    public function laporan(Request $request)
    {
        $today = Carbon::today();

        $loans = Loan::with([
                'member', 'application', 'loanProduct', 'collectionRecord',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1),
            ])
            ->where('status', 'ACTIVE')
            ->get()
            ->map(fn($l) => $this->enrichLoan($l, $today));

        // Collectability buckets
        $buckets = [
            'lancar'   => ['label' => 'Lancar (0 hari)',    'min' => 0,   'max' => 0],
            'b1_30'    => ['label' => '1 – 30 hari',        'min' => 1,   'max' => 30],
            'b31_60'   => ['label' => '31 – 60 hari',       'min' => 31,  'max' => 60],
            'b61_plus' => ['label' => '61 hari ke atas',    'min' => 61,  'max' => PHP_INT_MAX],
        ];

        foreach ($buckets as $key => &$bucket) {
            $subset = $loans->filter(fn($l) =>
                $l->_days_overdue >= $bucket['min'] && $l->_days_overdue <= $bucket['max']
            );
            $bucket['count']       = $subset->count();
            $bucket['outstanding'] = $subset->sum('outstanding');
            $bucket['arrears']     = $subset->sum(fn($l) => abs($l->_tunggakan));
            // member names for click-to-expand
            $bucket['members']     = $subset->map(fn($l) => [
                'name'     => $l->member->name,
                'overdue'  => $l->_days_overdue,
                'tunggakan'=> abs($l->_tunggakan),
            ])->values()->toArray();
        }
        unset($bucket);

        // NPL = sum of 1-30 + 31-60 + 61+
        $npl = $loans->filter(fn($l) => $l->_days_overdue > 0);

        // SP letter counts
        $spCounts = CollectionLetter::selectRaw('letter_type, count(*) as cnt')
            ->groupBy('letter_type')
            ->pluck('cnt', 'letter_type');

        $summary = [
            'total_nasabah'     => $loans->count(),
            'total_outstanding' => $loans->sum('outstanding'),
            'npl_nasabah'       => $npl->count(),
            'npl_outstanding'   => $npl->sum('outstanding'),
            'npl_pct'           => $loans->count() > 0
                ? round($npl->count() / $loans->count() * 100, 1)
                : 0,
        ];

        return view('admin.collection.laporan', compact('buckets', 'summary', 'spCounts', 'today', 'loans'));
    }

    /**
     * Export Laporan Collection as CSV.
     */
    public function exportLaporan(): StreamedResponse
    {
        $today = Carbon::today();

        $loans = Loan::with([
                'member', 'application', 'loanProduct', 'collectionRecord',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1),
            ])
            ->where('status', 'ACTIVE')
            ->get()
            ->map(fn($l) => $this->enrichLoan($l, $today));

        $buckets = [
            'lancar'   => ['label' => 'Lancar (0 hari)',  'min' => 0,  'max' => 0],
            'b1_30'    => ['label' => '1 – 30 hari',      'min' => 1,  'max' => 30],
            'b31_60'   => ['label' => '31 – 60 hari',     'min' => 31, 'max' => 60],
            'b61_plus' => ['label' => '61 hari ke atas',  'min' => 61, 'max' => PHP_INT_MAX],
        ];

        $totalCount = $loans->count();

        foreach ($buckets as $key => &$bucket) {
            $subset = $loans->filter(fn($l) =>
                $l->_days_overdue >= $bucket['min'] && $l->_days_overdue <= $bucket['max']
            );
            $bucket['count']       = $subset->count();
            $bucket['outstanding'] = $subset->sum('outstanding');
            $bucket['arrears']     = $subset->sum(fn($l) => abs($l->_tunggakan));
            $bucket['loans']       = $subset->values();
        }
        unset($bucket);

        $filename = 'laporan-collection-' . $today->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($buckets, $totalCount, $today) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            // Summary header
            fputcsv($out, ['LAPORAN COLLECTION — Per ' . $today->format('d/m/Y')], ';');
            fputcsv($out, [], ';');

            // Kolektabilitas table
            fputcsv($out, ['Kolektabilitas', 'Nasabah', 'Persentase', 'Outstanding (Rp)', 'Tunggakan (Rp)'], ';');
            foreach ($buckets as $bucket) {
                $pct = $totalCount > 0 ? number_format($bucket['count'] / $totalCount * 100, 1) . '%' : '0%';
                fputcsv($out, [
                    $bucket['label'],
                    $bucket['count'],
                    $pct,
                    $bucket['outstanding'],
                    $bucket['arrears'],
                ], ';');
            }
            fputcsv($out, [], ';');

            // Per-bucket member detail
            foreach ($buckets as $bucket) {
                if ($bucket['count'] === 0) continue;
                fputcsv($out, ['--- ' . $bucket['label'] . ' ---'], ';');
                fputcsv($out, ['No', 'Nama Nasabah', 'No. Anggota', 'Nomor Kontrak', 'Pinjaman (Rp)', 'Hari Telat', 'Tunggakan (Rp)'], ';');
                foreach ($bucket['loans'] as $i => $loan) {
                    $noPk = $loan->application?->notaris_deed_number
                          ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . date('Y', strtotime($loan->disbursed_date)));
                    fputcsv($out, [
                        $i + 1,
                        $loan->member->name,
                        $loan->member->member_no ?? '',
                        $noPk,
                        $loan->principal,
                        $loan->_days_overdue ?: '-',
                        abs($loan->_tunggakan) ?: '-',
                    ], ';');
                }
                fputcsv($out, [], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Data Master DHIA — extended master data view.
     */
    public function masterDhia(Request $request)
    {
        $search  = $request->input('q');
        $wilayah = $request->input('wilayah');
        $sortBy  = $request->input('sort_by');

        $query = Loan::with(['member', 'application', 'collectionRecord', 'loanProduct',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1)])
            ->where('status', 'ACTIVE')
            ->orderBy('id');

        if ($search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }
        if ($wilayah) {
            $query->whereHas('collectionRecord', fn($q) => $q->where('wilayah', 'ilike', "%{$wilayah}%"));
        }

        $today = Carbon::today();

        if ($sortBy) {
            $raw = $query->get();
            $raw->transform(fn($l) => $this->enrichLoan($l, $today));
            $loans = $this->applySort($raw, $sortBy)->values();
        } else {
            $loans = $query->paginate(100)->withQueryString();
            $loans->getCollection()->transform(fn($l) => $this->enrichLoan($l, $today));
        }

        $wilayahList = CollectionRecord::whereNotNull('wilayah')
            ->distinct()->pluck('wilayah')->sort()->values();

        return view('admin.collection.master-dhia', compact('loans', 'search', 'wilayah', 'wilayahList', 'today', 'sortBy'));
    }

    /**
     * Export Data Master DHIA as CSV.
     */
    public function exportMasterDhia(Request $request): StreamedResponse
    {
        $search  = $request->input('q');
        $wilayah = $request->input('wilayah');
        $sortBy  = $request->input('sort_by');

        $query = Loan::with(['member', 'application', 'collectionRecord', 'loanProduct',
                'payments' => fn($q) => $q->where('is_voided', false)->orderByDesc('trx_date')->limit(1)])
            ->where('status', 'ACTIVE')
            ->orderBy('id');

        if ($search) {
            $query->whereHas('member', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }
        if ($wilayah) {
            $query->whereHas('collectionRecord', fn($q) => $q->where('wilayah', 'ilike', "%{$wilayah}%"));
        }

        $today = Carbon::today();
        $loans = $query->get();
        $loans->transform(fn($l) => $this->enrichLoan($l, $today));
        $loans = $this->applySort($loans, $sortBy)->values();

        $filename = 'master-dhia-' . $today->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($loans) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'No', 'Tgl Pencairan', 'Nama Nasabah', 'Nomor Kontrak', 'Plafond (Rp)',
                'Tenor', 'Angsuran Ke', 'NJOP', 'LT (m²)', 'LB (m²)',
                'Sisa Pokok Hutang (Rp)', 'Angsuran Berjalan (Rp)', 'Tunggakan (Rp)',
                'Denda Keterlambatan (Rp)', 'Hari Keterlambatan',
                'Alamat KTP', 'Alamat Jaminan', 'Alamat Tinggal', 'Wilayah', 'No Telepon', 'Keterangan',
            ], ';');

            foreach ($loans as $i => $loan) {
                $rec   = $loan->collectionRecord;
                $noPk  = $loan->application?->notaris_deed_number
                      ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . date('Y', strtotime($loan->disbursed_date)));

                $alamatTinggal = $rec?->alamat_tinggal ?: ($loan->_alamat_tinggal ?? $loan->member->address ?? '');

                fputcsv($out, [
                    $i + 1,
                    Carbon::parse($loan->disbursed_date)->format('d/m/Y'),
                    $loan->member->name,
                    $noPk,
                    $loan->principal,
                    $loan->tenor_months,
                    $loan->_ke,
                    $loan->_dhia_njop ?? '',
                    $loan->_dhia_lt   ?? '',
                    $loan->_dhia_lb   ?? '',
                    $loan->outstanding,
                    $loan->monthly_installment,
                    $loan->_tunggakan < 0 ? abs($loan->_tunggakan) : '',
                    $loan->_denda > 0 ? $loan->_denda : '',
                    $loan->_days_overdue > 0 ? $loan->_days_overdue : '',
                    $loan->member->address ?? '',
                    $loan->_alamat_jaminan,
                    $alamatTinggal,
                    $rec?->wilayah ?? '',
                    $loan->member->phone ?? '',
                    $rec?->notes ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Individual loan collection detail.
     */
    public function show(Loan $loan)
    {
        $loan->load(['member', 'application', 'loanProduct', 'payments' => fn($q) => $q->orderByDesc('trx_date')]);

        $record  = CollectionRecord::firstOrNew(['loan_id' => $loan->id]);
        $letters = CollectionLetter::where('loan_id', $loan->id)
            ->with('creator')->orderByDesc('letter_date')->get();

        $today      = Carbon::today();
        $arrears    = $this->computeArrears($loan, $today);
        $monthsLate = $this->computeMonthsLate($loan, $today);

        // DHIA defaults pulled from loan application's collateral data
        $fd = $loan->application?->form_data ?? [];
        $dhiaDefaults = [
            'njop'          => $fd['nilai_agunan']  ?? null,
            'luas_tanah'    => $fd['luas_tanah']    ?? null,
            'luas_bangunan' => $fd['luas_bangunan'] ?? null,
        ];

        $petugasList = User::whereHas('roles', fn($q) => $q->whereIn('name',
                ['ADMIN_COLLECTION', 'SUPER_ADMIN']))
            ->orderBy('name')->get();

        $todayStr = now()->toDateString();
        $nextNumbers = [];
        foreach (\App\Models\CollectionLetter::TYPES as $type => $label) {
            $nextNumbers[$type] = CollectionLetterController::previewNumber($type, $todayStr);
        }

        return view('admin.collection.show', compact(
            'loan', 'record', 'letters', 'arrears', 'monthsLate', 'petugasList', 'nextNumbers', 'dhiaDefaults'
        ));
    }

    /**
     * Update collection record.
     */
    public function update(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'petugas_id'        => 'nullable|exists:users,id',
            'branch'            => 'nullable|string|max:80',
            'is_restruk'        => 'boolean',
            'last_contact_date' => 'nullable|date',
            'next_contact_date' => 'nullable|date',
            'notes'             => 'nullable|string|max:2000',
            'njop'              => 'nullable|integer|min:0',
            'luas_tanah'        => 'nullable|integer|min:0',
            'luas_bangunan'     => 'nullable|integer|min:0',
            'alamat_tinggal'    => 'nullable|string|max:500',
            'wilayah'           => 'nullable|string|max:100',
        ]);

        $data['is_restruk'] = $request->boolean('is_restruk');

        CollectionRecord::updateOrCreate(['loan_id' => $loan->id], $data);

        return back()->with('success', 'Catatan penagihan diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function enrichLoan(Loan $loan, Carbon $today): Loan
    {
        $disbursed     = Carbon::parse($loan->disbursed_date);
        $monthsElapsed = min((int) $disbursed->diffInMonths($today), $loan->tenor_months);
        $arrears       = $this->computeArrears($loan, $today);

        $loan->_ke               = $monthsElapsed;
        $loan->_bejalan_tgl      = $disbursed->copy()->addMonths($monthsElapsed)->format('d-M');
        $loan->_angsuran_berjalan = (int) $loan->monthly_installment;

        // Months & days overdue
        $monthsLate = $loan->monthly_installment > 0
            ? (int) ceil($arrears / $loan->monthly_installment)
            : 0;
        $loan->_bln_telat = $monthsLate;

        // Proper days overdue: date since first unpaid installment was due
        if ($arrears > 0 && $loan->monthly_installment > 0) {
            $monthsPaid      = (int) floor($loan->total_paid / $loan->monthly_installment);
            $firstUnpaidDate = $disbursed->copy()->addMonths($monthsPaid + 1);
            $loan->_days_overdue = max(0, (int) $firstUnpaidDate->diffInDays($today));
        } else {
            $loan->_days_overdue = 0;
        }

        $loan->_tunggakan        = -$arrears;
        $loan->_total_kewajiban  = (int) $loan->monthly_installment - $arrears;

        $lastPayment = $loan->payments->first();
        $loan->_realisasi_tgl         = $lastPayment ? Carbon::parse($lastPayment->trx_date)->format('d-M-y') : null;
        $loan->_realisasi_rp          = $lastPayment ? (int) $lastPayment->amount : 0;
        $loan->_realisasi_tunggakan   = $loan->_tunggakan + $loan->_realisasi_rp;
        $loan->_sisa_kewajiban        = (int) $loan->outstanding;
        $loan->_total_bayar           = (int) $loan->total_paid;

        // Denda keterlambatan: outstanding × daily rate × days overdue
        $dailyRate = $loan->loanProduct?->penalty_daily_rate_bp ?? 30;
        $loan->_denda = $loan->_days_overdue > 0
            ? (int) round($loan->outstanding * $dailyRate / 10000 * $loan->_days_overdue)
            : 0;
        $loan->_kota_jaminan          = $loan->application?->form_data['kota_agunan'] ?? '';
        $loan->_alamat_tinggal        = $loan->collectionRecord?->alamat_tinggal ?: ($loan->member->address ?? '');
        $loan->_alamat_jaminan        = $this->buildAlamatJaminan($loan);

        // DHIA property fields: collection_record override → fallback to loan application form_data
        $fd = $loan->application?->form_data ?? [];
        $rec = $loan->collectionRecord;
        $loan->_dhia_njop = $rec?->njop ?: ($fd['nilai_agunan'] ?? null);
        $loan->_dhia_lt   = $rec?->luas_tanah ?: ($fd['luas_tanah'] ?? null);
        $loan->_dhia_lb   = $rec?->luas_bangunan ?: ($fd['luas_bangunan'] ?? null);

        return $loan;
    }

    private function buildAlamatJaminan(Loan $loan): string
    {
        $fd    = $loan->application?->form_data ?? [];
        $parts = array_filter([
            $fd['komplek_agunan'] ?? '',
            isset($fd['jalan_agunan']) ? 'Jl. ' . $fd['jalan_agunan'] : '',
            isset($fd['no_agunan'])    ? 'No. ' . $fd['no_agunan'] : '',
            isset($fd['rt_agunan'], $fd['rw_agunan'])
                ? 'RT.' . $fd['rt_agunan'] . ' RW.' . $fd['rw_agunan'] : '',
            $fd['kelurahan_agunan']  ?? '',
            $fd['kecamatan_agunan']  ?? '',
            $fd['kota_agunan']       ?? '',
        ]);
        return implode(' ', $parts) ?: ($loan->member->address ?? '');
    }

    private function applySort(Collection $loans, ?string $sortBy): Collection
    {
        return match ($sortBy) {
            'alamat_ktp'     => $loans->sortBy(fn($l) => strtolower($l->member->address ?? '')),
            'alamat_jaminan' => $loans->sortBy(fn($l) => strtolower($l->_alamat_jaminan ?? '')),
            'alamat_tinggal' => $loans->sortBy(fn($l) => strtolower($l->_alamat_tinggal ?? '')),
            default          => $loans,
        };
    }

    private function computeArrears(Loan $loan, Carbon $today): int
    {
        $disbursed     = Carbon::parse($loan->disbursed_date);
        $monthsElapsed = min((int) $disbursed->diffInMonths($today), $loan->tenor_months);
        $expected      = $monthsElapsed * $loan->monthly_installment;
        return (int) max(0, $expected - $loan->total_paid);
    }

    private function computeMonthsLate(Loan $loan, Carbon $today): int
    {
        if ($loan->monthly_installment <= 0) return 0;
        return (int) ceil($this->computeArrears($loan, $today) / $loan->monthly_installment);
    }

    private function computeTotalKewajiban(Loan $loan, Carbon $today): int
    {
        return (int) $loan->monthly_installment - $this->computeArrears($loan, $today);
    }
}
