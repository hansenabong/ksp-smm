<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Member;
use App\Models\ShuMemberAllocation;
use App\Models\ShuPeriod;
use App\Models\SavingTransaction;
use App\Services\Accounting\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShuController extends Controller
{
    public function __construct(private PostingService $posting) {}

    public function index()
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $periods = ShuPeriod::orderByDesc('year')->get();

        return view('admin.shu.index', compact('periods'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);

        $suggestedYear = now()->year;

        // Default allocation percentages (bp)
        $defaults = [
            'cadangan_umum_bp' => 2000,
            'jasa_simpanan_bp' => 3000,
            'jasa_usaha_bp'    => 2500,
            'pengurus_bp'      => 750,
            'pegawai_bp'       => 500,
            'sosial_bp'        => 125,
            'pendidikan_bp'    => 125,
        ];

        return view('admin.shu.create', compact('suggestedYear', 'defaults'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);

        $data = $request->validate([
            'year'             => ['required', 'integer', 'min:2000', 'max:2099', 'unique:shu_periods,year'],
            'date_from'        => ['required', 'date'],
            'date_to'          => ['required', 'date', 'after_or_equal:date_from'],
            'cadangan_umum_bp' => ['required', 'integer', 'min:0', 'max:10000'],
            'jasa_simpanan_bp' => ['required', 'integer', 'min:0', 'max:10000'],
            'jasa_usaha_bp'    => ['required', 'integer', 'min:0', 'max:10000'],
            'pengurus_bp'      => ['required', 'integer', 'min:0', 'max:10000'],
            'pegawai_bp'       => ['required', 'integer', 'min:0', 'max:10000'],
            'sosial_bp'        => ['required', 'integer', 'min:0', 'max:10000'],
            'pendidikan_bp'    => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        $totalBp = $data['cadangan_umum_bp'] + $data['jasa_simpanan_bp'] + $data['jasa_usaha_bp']
            + $data['pengurus_bp'] + $data['pegawai_bp'] + $data['sosial_bp'] + $data['pendidikan_bp'];

        if ($totalBp !== 10000) {
            throw ValidationException::withMessages([
                'total_bp' => "Total persentase harus 100% (10.000 bp). Sekarang: {$totalBp} bp."
            ]);
        }

        $period = DB::transaction(function () use ($data) {
            $shuBruto = $this->computeShuBruto($data['date_from'], $data['date_to']);

            $period = ShuPeriod::create([
                'year'             => $data['year'],
                'date_from'        => $data['date_from'],
                'date_to'          => $data['date_to'],
                'shu_bruto'        => $shuBruto,
                'cadangan_umum_bp' => $data['cadangan_umum_bp'],
                'jasa_simpanan_bp' => $data['jasa_simpanan_bp'],
                'jasa_usaha_bp'    => $data['jasa_usaha_bp'],
                'pengurus_bp'      => $data['pengurus_bp'],
                'pegawai_bp'       => $data['pegawai_bp'],
                'sosial_bp'        => $data['sosial_bp'],
                'pendidikan_bp'    => $data['pendidikan_bp'],
                'status'           => 'DRAFT',
                'created_by'       => auth()->id(),
            ]);

            $period->recalculateAmounts();
            $period->save();

            $this->computeMemberAllocations($period);

            return $period;
        });

        return redirect()->route('admin.shu.show', $period)
            ->with('success', "Periode SHU {$period->year} berhasil dibuat.");
    }

    public function show(ShuPeriod $shuPeriod)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $shuPeriod->load(['memberAllocations.member', 'creator', 'finalizer', 'journalEntry']);
        $allocations = $shuPeriod->memberAllocations->sortByDesc('shu_total');

        return view('admin.shu.show', compact('shuPeriod', 'allocations'));
    }

    public function recalculate(ShuPeriod $shuPeriod)
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);

        if ($shuPeriod->isFinalized()) {
            return back()->withErrors(['status' => 'Periode sudah FINALIZED, tidak dapat dihitung ulang.']);
        }

        DB::transaction(function () use ($shuPeriod) {
            $shuPeriod->shu_bruto = $this->computeShuBruto($shuPeriod->date_from, $shuPeriod->date_to);
            $shuPeriod->recalculateAmounts();
            $shuPeriod->save();

            $shuPeriod->memberAllocations()->delete();
            $this->computeMemberAllocations($shuPeriod);
        });

        return back()->with('success', 'Data SHU berhasil dihitung ulang.');
    }

    public function finalize(ShuPeriod $shuPeriod)
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);

        if ($shuPeriod->isFinalized()) {
            return back()->withErrors(['status' => 'Periode sudah FINALIZED.']);
        }

        if ($shuPeriod->shu_bruto <= 0) {
            return back()->withErrors(['shu_bruto' => 'SHU Bruto harus positif untuk dapat difinalisasi.']);
        }

        DB::transaction(function () use ($shuPeriod) {
            $acc3212 = $this->posting->accountByCode('3212'); // SHU Tahun Berjalan
            $acc3112 = $this->posting->accountByCode('3112'); // Dana Cadangan Umum
            $acc2130 = $this->posting->accountByCode('2130'); // Dana SHU Belum Dibagi
            $acc3213 = $this->posting->accountByCode('3213'); // Dana Pengurus
            $acc3214 = $this->posting->accountByCode('3214'); // Dana Pegawai
            $acc3215 = $this->posting->accountByCode('3215'); // Dana Sosial
            $acc3216 = $this->posting->accountByCode('3216'); // Dana Pendidikan

            $memberPortion = (int) ($shuPeriod->jasa_simpanan_amount + $shuPeriod->jasa_usaha_amount);

            // Build credit lines (only include non-zero amounts)
            $creditLines = [];
            if ($shuPeriod->cadangan_umum_amount > 0) {
                $creditLines[] = ['account_id' => $acc3112->id, 'dc' => 'C', 'amount' => $shuPeriod->cadangan_umum_amount, 'memo' => 'Dana Cadangan Umum'];
            }
            if ($memberPortion > 0) {
                $creditLines[] = ['account_id' => $acc2130->id, 'dc' => 'C', 'amount' => $memberPortion, 'memo' => 'Dana SHU Anggota (Jasa Simpanan + Jasa Usaha)'];
            }
            if ($shuPeriod->pengurus_amount > 0) {
                $creditLines[] = ['account_id' => $acc3213->id, 'dc' => 'C', 'amount' => $shuPeriod->pengurus_amount, 'memo' => 'Dana Pengurus'];
            }
            if ($shuPeriod->pegawai_amount > 0) {
                $creditLines[] = ['account_id' => $acc3214->id, 'dc' => 'C', 'amount' => $shuPeriod->pegawai_amount, 'memo' => 'Dana Pegawai'];
            }
            if ($shuPeriod->sosial_amount > 0) {
                $creditLines[] = ['account_id' => $acc3215->id, 'dc' => 'C', 'amount' => $shuPeriod->sosial_amount, 'memo' => 'Dana Sosial'];
            }
            if ($shuPeriod->pendidikan_amount > 0) {
                $creditLines[] = ['account_id' => $acc3216->id, 'dc' => 'C', 'amount' => $shuPeriod->pendidikan_amount, 'memo' => 'Dana Pendidikan'];
            }

            // Total credit must equal shu_bruto for the debit line to balance.
            // Due to rounding, use actual sum of credits for the debit amount.
            $totalCredit = collect($creditLines)->sum('amount');

            if ($totalCredit <= 0) {
                throw ValidationException::withMessages(['shu' => 'Tidak ada alokasi SHU yang dapat diposting.']);
            }

            $lines = array_merge(
                [['account_id' => $acc3212->id, 'dc' => 'D', 'amount' => $totalCredit, 'memo' => "Distribusi SHU Tahun {$shuPeriod->year}"]],
                $creditLines
            );

            $je = $this->posting->createEntry([
                'trx_date'    => $shuPeriod->date_to,
                'source_type' => ShuPeriod::class,
                'source_id'   => $shuPeriod->id,
                'description' => "Distribusi SHU Tahun {$shuPeriod->year}",
                'created_by'  => auth()->id(),
            ], $lines);

            $shuPeriod->update([
                'status'          => 'FINALIZED',
                'journal_entry_id'=> $je->id,
                'finalized_by'    => auth()->id(),
                'finalized_at'    => now(),
            ]);
        });

        return redirect()->route('admin.shu.show', $shuPeriod)
            ->with('success', "SHU Tahun {$shuPeriod->year} berhasil difinalisasi dan jurnal telah dibuat.");
    }

    public function destroy(ShuPeriod $shuPeriod)
    {
        abort_unless(auth()->user()->hasRole('SUPER_ADMIN'), 403);

        if ($shuPeriod->isFinalized()) {
            return back()->withErrors(['status' => 'Periode FINALIZED tidak dapat dihapus.']);
        }

        $shuPeriod->delete();

        return redirect()->route('admin.shu.index')
            ->with('success', "Periode SHU {$shuPeriod->year} berhasil dihapus.");
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /** Compute SHU Bruto = REVENUE - EXPENSE for the period (from journal entries). */
    private function computeShuBruto(string $dateFrom, string $dateTo): int
    {
        $rows = Account::query()
            ->whereIn('accounts.type', ['REVENUE', 'EXPENSE'])
            ->where('accounts.is_active', true)
            ->whereDoesntHave('children')
            ->leftJoin('journal_lines as jl', 'jl.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->select([
                'accounts.type',
                'accounts.normal_balance',
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'D' THEN jl.amount ELSE 0 END), 0) AS period_debit"),
                DB::raw("COALESCE(SUM(CASE
                    WHEN je.id IS NOT NULL AND je.is_voided = false
                     AND je.trx_date >= ? AND je.trx_date <= ?
                     AND jl.dc = 'C' THEN jl.amount ELSE 0 END), 0) AS period_credit"),
            ])
            ->addBinding([$dateFrom, $dateTo, $dateFrom, $dateTo], 'select')
            ->groupBy('accounts.type', 'accounts.normal_balance')
            ->get();

        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($rows as $r) {
            $net = $r->normal_balance === 'C'
                ? (int) $r->period_credit - (int) $r->period_debit
                : (int) $r->period_debit  - (int) $r->period_credit;

            if ($r->type === 'REVENUE') {
                $totalRevenue += $net;
            } else {
                $totalExpense += $net;
            }
        }

        return max(0, $totalRevenue - $totalExpense);
    }

    /** Compute and persist per-member SHU allocations. */
    private function computeMemberAllocations(ShuPeriod $period): void
    {
        $dateFrom = $period->date_from->toDateString();
        $dateTo   = $period->date_to->toDateString();

        $members = Member::active()->get();

        // Total savings across all active members (for proportional allocation)
        $totalAvgSavings = 0;
        $totalLoanPayments = 0;

        $memberData = [];

        foreach ($members as $member) {
            // avg_savings = (balance before date_from + balance at date_to) / 2
            $balBefore = $this->memberSavingBalance($member->id, null, $dateFrom);
            $balAt     = $this->memberSavingBalance($member->id, null, $dateTo);
            $avgSavings = (int) round(($balBefore + $balAt) / 2);

            // total_loan_payments = sum of payments during period
            $loanPayments = LoanPayment::query()
                ->join('loans', 'loans.id', '=', 'loan_payments.loan_id')
                ->where('loans.member_id', $member->id)
                ->where('loan_payments.is_voided', false)
                ->whereBetween('loan_payments.trx_date', [$dateFrom, $dateTo])
                ->sum('loan_payments.amount');

            $memberData[$member->id] = [
                'member'             => $member,
                'avg_savings'        => max(0, $avgSavings),
                'total_loan_payments'=> (int) $loanPayments,
            ];

            $totalAvgSavings   += max(0, $avgSavings);
            $totalLoanPayments += (int) $loanPayments;
        }

        // Allocate jasa_simpanan proportional to avg_savings
        // Allocate jasa_usaha proportional to total_loan_payments
        $jasaSimpananPool = (int) $period->jasa_simpanan_amount;
        $jasaUsahaPool    = (int) $period->jasa_usaha_amount;

        $inserts = [];

        foreach ($memberData as $memberId => $d) {
            $shuSimpanan = $totalAvgSavings > 0
                ? (int) round($jasaSimpananPool * $d['avg_savings'] / $totalAvgSavings)
                : 0;

            $shuUsaha = $totalLoanPayments > 0
                ? (int) round($jasaUsahaPool * $d['total_loan_payments'] / $totalLoanPayments)
                : 0;

            if ($shuSimpanan === 0 && $shuUsaha === 0 && $d['avg_savings'] === 0 && $d['total_loan_payments'] === 0) {
                continue; // Skip members with no activity
            }

            $inserts[] = [
                'shu_period_id'      => $period->id,
                'member_id'          => $memberId,
                'avg_savings'        => $d['avg_savings'],
                'total_loan_payments'=> $d['total_loan_payments'],
                'shu_simpanan'       => $shuSimpanan,
                'shu_usaha'          => $shuUsaha,
                'shu_total'          => $shuSimpanan + $shuUsaha,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        if (!empty($inserts)) {
            ShuMemberAllocation::insert($inserts);
        }
    }

    /** Cumulative saving balance for a member up to (but not including) $before, or up to $upTo. */
    private function memberSavingBalance(int $memberId, ?string $before, ?string $upTo): int
    {
        $query = SavingTransaction::where('member_id', $memberId)
            ->where('is_voided', false);

        if ($before !== null) {
            $query->where('trx_date', '<', $before);
        }
        if ($upTo !== null) {
            $query->where('trx_date', '<=', $upTo);
        }

        $deposit    = (clone $query)->where('type', 'DEPOSIT')->sum('amount');
        $withdrawal = (clone $query)->where('type', 'WITHDRAWAL')->sum('amount');

        return (int) $deposit - (int) $withdrawal;
    }
}
