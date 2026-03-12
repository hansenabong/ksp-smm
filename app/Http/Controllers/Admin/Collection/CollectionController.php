<?php

namespace App\Http\Controllers\Admin\Collection;

use App\Http\Controllers\Controller;
use App\Models\CollectionRecord;
use App\Models\CollectionLetter;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        $loans = $query->paginate(100)->withQueryString();
        $today = Carbon::today();
        $loans->getCollection()->transform(fn($l) => $this->enrichLoan($l, $today));

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
            'target'            => (int) ($allActive->sum(fn($l) => $this->computeTotalKewajiban($l, $today)) * 0.65),
            'total_realisasi'   => $allActive->sum(fn($l) => optional($l->payments->first())->amount ?? 0),
        ];

        $petugasList = User::whereHas('roles', fn($q) => $q->whereIn('name',
                ['ADMIN_COLLECTION', 'PETUGAS', 'SUPER_ADMIN', 'ADMIN', 'MANAGER']))
            ->orderBy('name')->get();

        return view('admin.collection.index', compact(
            'loans', 'totals', 'petugasList', 'levelFilter', 'petugasFilter', 'search', 'today'
        ));
    }

    /**
     * Laporan Collection — collectability summary report.
     */
    public function laporan(Request $request)
    {
        $today = Carbon::today();

        $loans = Loan::with([
                'member', 'application', 'collectionRecord',
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
        }
        unset($bucket);

        // NPL = sum of 1-30 + 31-60 + 61+
        $npl = $loans->filter(fn($l) => $l->_days_overdue > 0);

        // SP letter counts
        $spCounts = CollectionLetter::selectRaw('letter_type, count(*) as cnt')
            ->groupBy('letter_type')
            ->pluck('cnt', 'letter_type');

        $summary = [
            'total_nasabah'   => $loans->count(),
            'total_outstanding' => $loans->sum('outstanding'),
            'npl_nasabah'     => $npl->count(),
            'npl_outstanding' => $npl->sum('outstanding'),
            'npl_pct'         => $loans->count() > 0
                ? round($npl->count() / $loans->count() * 100, 1)
                : 0,
        ];

        return view('admin.collection.laporan', compact('buckets', 'summary', 'spCounts', 'today', 'loans'));
    }

    /**
     * Data Master DHIA — extended master data view.
     */
    public function masterDhia(Request $request)
    {
        $search  = $request->input('q');
        $wilayah = $request->input('wilayah');

        $query = Loan::with(['member', 'application', 'collectionRecord',
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
        $loans = $query->paginate(100)->withQueryString();
        $loans->getCollection()->transform(fn($l) => $this->enrichLoan($l, $today));

        $wilayahList = CollectionRecord::whereNotNull('wilayah')
            ->distinct()->pluck('wilayah')->sort()->values();

        return view('admin.collection.master-dhia', compact('loans', 'search', 'wilayah', 'wilayahList', 'today'));
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

        $petugasList = User::whereHas('roles', fn($q) => $q->whereIn('name',
                ['ADMIN_COLLECTION', 'PETUGAS', 'SUPER_ADMIN', 'ADMIN', 'MANAGER']))
            ->orderBy('name')->get();

        return view('admin.collection.show', compact(
            'loan', 'record', 'letters', 'arrears', 'monthsLate', 'petugasList'
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
        $loan->_bln_telat = -$monthsLate;

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
        $loan->_realisasi_tunggakan   = $loan->_tunggakan;
        $loan->_sisa_kewajiban        = (int) $loan->outstanding;
        $loan->_total_bayar           = (int) $loan->outstanding;
        $loan->_kota_jaminan          = $loan->application?->form_data['kota_agunan'] ?? '';
        $loan->_alamat_tinggal        = $loan->member->address ?? '';
        $loan->_alamat_jaminan        = $this->buildAlamatJaminan($loan);

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
