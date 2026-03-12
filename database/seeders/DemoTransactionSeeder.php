<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\CashCategory;
use App\Models\LoanApplication;
use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Models\User;
use App\Services\Cash\CashTransactionService;
use App\Services\Loans\LoanService;
use App\Services\Savings\SavingsService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds 6 months of realistic KSP demo transactions.
 * Requires: DemoMasterSeeder + AccountingSeeder + CashCategorySeeder to have run first.
 *
 * Simulates Sep 2025 → Feb 2026 (6 months).
 *
 * Scenarios covered:
 *  - Cash income / expense / transfer (monthly cycle)
 *  - Simpanan wajib & sukarela + penarikan
 *  - Loan applications: 8 approved (various pay progress), 2 pending
 *  - Settled loan   : mikro 6-month, fully paid → status SETTLED
 *  - Late-payment   : 2nd installment paid 15 days late with penalty
 *  - Notaris data   : first 2 approved applications have deed info
 */
class DemoTransactionSeeder extends Seeder
{
    private CashTransactionService $cashSvc;
    private SavingsService $savingSvc;
    private LoanService $loanSvc;

    private int $adminId;
    private int $petugasId;
    private int $bendaharaId;

    private CashAccount $kasTunai;
    private CashAccount $bankBri;

    private SavingProduct $simpWajib;
    private SavingProduct $simpSukarela;

    private LoanProduct $prodRegular;
    private LoanProduct $prodMikro;

    /** @var Member[] */
    private array $members;

    /** Cash category ID lookup: "name|TYPE" => id */
    private array $catMap = [];

    public function run(): void
    {
        $this->cashSvc   = app(CashTransactionService::class);
        $this->savingSvc = app(SavingsService::class);
        $this->loanSvc   = app(LoanService::class);

        // Resolve users (fall back gracefully if a role has no users)
        $this->adminId     = User::role('SUPER_ADMIN')->first()?->id
                          ?? User::first()->id;
        $this->petugasId   = User::role('ADMIN_DATA')->first()?->id    ?? $this->adminId;
        $this->bendaharaId = User::role('TREASURY')->first()?->id ?? $this->adminId;

        // Resolve master data
        $this->kasTunai     = CashAccount::where('name', 'Kas Tunai')->firstOrFail();
        $this->bankBri      = CashAccount::where('name', 'Bank BRI')->firstOrFail();
        $this->simpWajib    = SavingProduct::where('name', 'Simpanan Wajib')->firstOrFail();
        $this->simpSukarela = SavingProduct::where('name', 'Simpanan Sukarela')->firstOrFail();
        $this->prodRegular  = LoanProduct::where('name', 'Pinjaman Reguler')->firstOrFail();
        $this->prodMikro    = LoanProduct::where('name', 'Pinjaman Mikro')->firstOrFail();
        $this->members      = Member::active()->orderBy('member_no')->get()->all();

        if (empty($this->members)) {
            $this->command->warn('No active members found. Run DemoMasterSeeder first.');
            return;
        }

        // Load cash categories into local lookup map
        $this->loadCategories();

        // ── 1. MODAL AWAL (Sep 1, 2025) ────────────────────────────────────
        $this->seedInitialBalance();

        // ── 2. MONTHLY CYCLE: Sep 2025 → Feb 2026 ──────────────────────────
        foreach (['2025-09-01', '2025-10-01', '2025-11-01', '2025-12-01', '2026-01-01', '2026-02-01'] as $monthStart) {
            $this->seedMonth($monthStart);
        }

        // ── 3. LOANS ────────────────────────────────────────────────────────
        $this->seedLoans();
    }

    // =========================================================================
    // CATEGORY HELPERS
    // =========================================================================
    private function loadCategories(): void
    {
        foreach (CashCategory::all() as $cat) {
            $this->catMap["{$cat->name}|{$cat->type}"] = $cat->id;
        }
    }

    private function catId(string $name, string $type): ?int
    {
        return $this->catMap["{$name}|{$type}"] ?? null;
    }

    // =========================================================================
    // INITIAL BALANCE
    // =========================================================================
    private function seedInitialBalance(): void
    {
        // Idempotent guard
        if (\App\Models\CashTransaction::where('to_cash_account_id', $this->kasTunai->id)
                ->where('description', 'LIKE', 'Modal awal%')
                ->exists()) {
            return;
        }

        // Rp 300.000.000 → Kas Tunai
        $this->cashSvc->createIncome([
            'trx_date'           => '2025-09-01',
            'to_cash_account_id' => $this->kasTunai->id,
            'cash_category_id'   => $this->catId('Modal / Saldo Awal', 'INCOME'),
            'amount'             => 300_000_000,
            'description'        => 'Modal awal koperasi — Kas Tunai',
        ], $this->adminId);

        // Rp 200.000.000 → Bank BRI
        $this->cashSvc->createIncome([
            'trx_date'           => '2025-09-01',
            'to_cash_account_id' => $this->bankBri->id,
            'cash_category_id'   => $this->catId('Modal / Saldo Awal', 'INCOME'),
            'amount'             => 200_000_000,
            'description'        => 'Modal awal koperasi — Bank BRI',
        ], $this->adminId);

        // Setor sebagian ke Bank BRI
        $this->cashSvc->createTransfer([
            'trx_date'             => '2025-09-02',
            'from_cash_account_id' => $this->kasTunai->id,
            'to_cash_account_id'   => $this->bankBri->id,
            'cash_category_id'     => $this->catId('Setor Bank', 'TRANSFER'),
            'amount'               => 50_000_000,
            'description'          => 'Setor tunai ke Bank BRI',
        ], $this->bendaharaId);
    }

    // =========================================================================
    // MONTHLY CYCLE
    // =========================================================================
    private function seedMonth(string $monthStart): void
    {
        $date        = Carbon::parse($monthStart);
        $monthLabel  = $date->translatedFormat('F Y');
        $depositDate = $date->copy()->addDays(4)->toDateString();  // tanggal 5
        $expenseDate = $date->copy()->addDays(9)->toDateString();  // tanggal 10

        // Guard: skip if savings already seeded for this month
        if (\App\Models\SavingTransaction::where('trx_date', $depositDate)
                ->where('saving_product_id', $this->simpWajib->id)
                ->exists()) {
            return;
        }

        // ── Simpanan Wajib — semua anggota (Rp 100.000/bulan) ───────────────
        foreach ($this->members as $member) {
            $this->savingSvc->deposit([
                'trx_date'          => $depositDate,
                'member_id'         => $member->id,
                'saving_product_id' => $this->simpWajib->id,
                'cash_account_id'   => $this->kasTunai->id,
                'amount'            => 100_000,
                'description'       => "Simpanan wajib {$monthLabel}",
            ], $this->petugasId);
        }

        // ── Simpanan Sukarela — 8 anggota pertama ───────────────────────────
        $voluntaryAmounts = [50_000, 100_000, 200_000, 150_000, 75_000, 250_000, 100_000, 500_000];
        foreach (array_slice($this->members, 0, 8) as $i => $member) {
            $this->savingSvc->deposit([
                'trx_date'          => $depositDate,
                'member_id'         => $member->id,
                'saving_product_id' => $this->simpSukarela->id,
                'cash_account_id'   => $this->kasTunai->id,
                'amount'            => $voluntaryAmounts[$i],
                'description'       => "Simpanan sukarela {$monthLabel}",
            ], $this->petugasId);
        }

        // ── Penarikan Sukarela — 2 anggota (mulai Oktober) ──────────────────
        if ($date->month > 9 || $date->year > 2025) {
            $withdrawDate = $date->copy()->addDays(14)->toDateString();
            foreach (array_slice($this->members, 5, 2) as $i => $member) {
                $amt = [100_000, 75_000][$i] ?? 100_000;
                $bal = $this->savingSvc->getMemberSavingBalance($member->id, $this->simpSukarela->id);
                if ($bal >= $amt) {
                    $this->savingSvc->withdraw([
                        'trx_date'          => $withdrawDate,
                        'member_id'         => $member->id,
                        'saving_product_id' => $this->simpSukarela->id,
                        'cash_account_id'   => $this->kasTunai->id,
                        'amount'            => $amt,
                        'description'       => "Penarikan simpanan sukarela {$monthLabel}",
                    ], $this->petugasId);
                }
            }
        }

        // ── Beban Operasional ────────────────────────────────────────────────
        $expenses = [
            ['amount' => 5_000_000, 'cat' => 'Gaji / Insentif',   'desc' => "Gaji karyawan {$monthLabel}"],
            ['amount' => 500_000,   'cat' => 'Biaya Admin / Bank', 'desc' => "Biaya admin bank {$monthLabel}"],
            ['amount' => 300_000,   'cat' => 'Operasional',        'desc' => "Listrik & air {$monthLabel}"],
            ['amount' => 200_000,   'cat' => 'Pembelian / ATK',    'desc' => "ATK kantor {$monthLabel}"],
        ];

        foreach ($expenses as $exp) {
            if ($this->cashSvc->getBalance($this->kasTunai->id) >= $exp['amount']) {
                $this->cashSvc->createExpense([
                    'trx_date'             => $expenseDate,
                    'from_cash_account_id' => $this->kasTunai->id,
                    'cash_category_id'     => $this->catId($exp['cat'], 'EXPENSE'),
                    'amount'               => $exp['amount'],
                    'description'          => $exp['desc'],
                ], $this->bendaharaId);
            }
        }

        // ── Pendapatan Jasa Giro (Oktober, Desember, Januari) ───────────────
        if (in_array($date->month, [10, 12, 1])) {
            $this->cashSvc->createIncome([
                'trx_date'           => $date->copy()->addDays(19)->toDateString(),
                'to_cash_account_id' => $this->bankBri->id,
                'cash_category_id'   => $this->catId('Pendapatan Jasa', 'INCOME'),
                'amount'             => 1_500_000,
                'description'        => "Pendapatan jasa giro {$monthLabel}",
            ], $this->bendaharaId);
        }
    }

    // =========================================================================
    // LOANS
    // =========================================================================
    private function seedLoans(): void
    {
        // Guard: skip entirely if member[0] already has applications
        if (LoanApplication::where('member_id', $this->members[0]->id)->exists()) {
            return;
        }

        $managerId = $this->adminId;

        /**
         * Columns: [member_idx, product, principal, tenor, apply_date, approve_date, paid_months, late_on_installment]
         *
         *   late_on_installment: installment number to pay 15 days late (0 = none)
         *
         * Members 0-9: standard mix of active / pending loans
         * Member 10  : settled mikro (6/6 paid → SETTLED status)
         * Member 11  : regular with 2nd installment 15 days late (penalty applied)
         */
        $scenarios = [
            [0,  'regular', 5_000_000,  12, '2025-09-05', '2025-09-08',  5, 0],
            [1,  'regular', 10_000_000, 24, '2025-09-10', '2025-09-15',  5, 0],
            [2,  'mikro',   2_000_000,  6,  '2025-10-01', '2025-10-05',  4, 0],
            [3,  'regular', 7_500_000,  12, '2025-10-15', '2025-10-20',  4, 0],
            [4,  'mikro',   3_000_000,  6,  '2025-11-01', '2025-11-07',  3, 0],
            [5,  'regular', 15_000_000, 24, '2025-11-10', '2025-11-15',  3, 0],
            [6,  'mikro',   1_500_000,  6,  '2025-12-01', '2025-12-05',  2, 0],
            [7,  'regular', 8_000_000,  18, '2026-01-05', '2026-01-10',  1, 0],
            // Pending (not yet approved)
            [8,  'regular', 4_000_000,  12, '2026-02-01', null,          0, 0],
            [9,  'mikro',   2_500_000,  6,  '2026-02-10', null,          0, 0],
            // Settled: 6-month mikro, all 6 installments paid
            [10, 'mikro',   1_800_000,  6,  '2025-09-15', '2025-09-18',  6, 0],
            // Late payment: 2nd installment 15 days overdue
            [11, 'regular', 4_500_000,  12, '2025-10-20', '2025-10-25',  3, 2],
        ];

        // Notaris deed info for the two largest early applications
        $notarisData = [
            0 => [
                'notaris_name'        => 'Notaris Hendra Kusuma, S.H.',
                'notaris_deed_number' => '12/NOT/IX/2025',
                'notaris_deed_date'   => '2025-09-07',
                'notaris_city'        => 'Jakarta',
            ],
            1 => [
                'notaris_name'        => 'Notaris Sri Wahyuni, S.H., M.Kn.',
                'notaris_deed_number' => '45/NOT/IX/2025',
                'notaris_deed_date'   => '2025-09-14',
                'notaris_city'        => 'Bandung',
            ],
        ];

        foreach ($scenarios as [$idx, $prodKey, $principal, $tenor, $applyDate, $approveDate, $paidMonths, $lateOn]) {
            $member  = $this->members[$idx];
            $product = $prodKey === 'regular' ? $this->prodRegular : $this->prodMikro;

            $appData = [
                'trx_date'                 => $applyDate,
                'member_id'                => $member->id,
                'loan_product_id'          => $product->id,
                'principal'                => $principal,
                'tenor_months'             => $tenor,
                'interest_monthly_percent' => $product->interest_rate_monthly_bp / 100.0,
                'admin_monthly'            => $product->admin_fee,
                'insurance_monthly'        => 0,
                'method'                   => $product->method,
                'interest_rate_monthly_bp' => $product->interest_rate_monthly_bp,
                'admin_fee'                => $product->admin_fee,
                'status'                   => 'PENDING',
                'created_by'               => $this->petugasId,
            ];

            if (isset($notarisData[$idx])) {
                $appData = array_merge($appData, $notarisData[$idx]);
            }

            $app = LoanApplication::create($appData);

            if ($approveDate === null) {
                continue;
            }

            $loan = $this->loanSvc->approveAndDisburse($app->id, [
                'trx_date'             => $approveDate,
                'from_cash_account_id' => $this->kasTunai->id,
                'approval_note'        => 'Disetujui — memenuhi syarat',
            ], $managerId);

            $disbursedDate = Carbon::parse($approveDate);

            for ($m = 1; $m <= $paidMonths; $m++) {
                $loan->refresh();
                if ($loan->status !== 'ACTIVE') {
                    break;
                }

                $payDate       = $disbursedDate->copy()->addMonths($m)->toDateString();
                $penaltyDays   = 0;
                $penaltyAmount = 0;

                // Apply late penalty on the designated installment
                if ($lateOn > 0 && $m === $lateOn) {
                    $penaltyDays   = 15;
                    $payDate       = $disbursedDate->copy()->addMonths($m)->addDays($penaltyDays)->toDateString();
                    $penaltyAmount = (int) round(
                        $loan->outstanding * $product->penalty_daily_rate_bp / 10000 * $penaltyDays
                    );
                }

                $this->loanSvc->payInstallment($loan->id, [
                    'trx_date'        => $payDate,
                    'amount'          => min($loan->monthly_installment, $loan->outstanding),
                    'cash_account_id' => $this->kasTunai->id,
                    'penalty_days'    => $penaltyDays,
                    'penalty_amount'  => $penaltyAmount,
                    'note'            => $penaltyDays > 0
                        ? "Angsuran ke-{$m} (terlambat {$penaltyDays} hari)"
                        : "Angsuran ke-{$m}",
                ], $this->petugasId);
            }
        }
    }
}
