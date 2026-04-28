<?php

namespace Database\Seeders;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Simulates overdue loans for demo/testing.
 * Backdates disbursed_date AND resets total_paid so the on-the-fly
 * overdue calculation (monthsElapsed × installment - total_paid) produces
 * real arrears regardless of the loan's original disbursement date.
 *
 * Usage: php artisan db:seed --class=CollectionOverdueSeeder
 */
class CollectionOverdueSeeder extends Seeder
{
    public function run(): void
    {
        $loans = Loan::with('member')
            ->where('status', 'ACTIVE')
            ->orderBy('id')
            ->get();

        if ($loans->count() < 6) {
            $this->command->warn('Need at least 6 active loans. Run DemoTransactionSeeder first.');
            return;
        }

        $today = Carbon::today();

        // [loan_index => [months_overdue, paid_months]]
        // disbursed_date will be set to (paid + overdue + 1) months ago + 5 days
        // so monthsElapsed = paid + overdue, arrears = overdue × installment
        $scenarios = [
            0 => ['overdue' => 1, 'paid' => 2, 'label' => '1 bln nunggak  → bucket 1–30 hari'],
            1 => ['overdue' => 2, 'paid' => 2, 'label' => '2 bln nunggak  → bucket 31–60 hari'],
            2 => ['overdue' => 3, 'paid' => 1, 'label' => '3 bln nunggak  → bucket 61+ hari'],
            3 => ['overdue' => 4, 'paid' => 1, 'label' => '4 bln nunggak  → bucket 61+ hari'],
            4 => ['overdue' => 5, 'paid' => 0, 'label' => '5 bln nunggak  → bucket 61+ hari'],
            5 => ['overdue' => 6, 'paid' => 0, 'label' => '6 bln nunggak  → bucket 61+ hari'],
        ];

        foreach ($scenarios as $idx => $s) {
            /** @var Loan $loan */
            $loan = $loans->get($idx);
            if (!$loan || $loan->monthly_installment <= 0) {
                continue;
            }

            // Backdate so monthsElapsed = paid + overdue (guaranteed)
            $totalMonthsBack = $s['paid'] + $s['overdue'];
            $newDisbursedDate = $today->copy()->subMonths($totalMonthsBack)->subDays(5)->toDateString();

            $newTotalPaid   = (int) ($s['paid'] * $loan->monthly_installment);
            $newOutstanding = max(0, (int) $loan->total_payable - $newTotalPaid);

            DB::table('loans')->where('id', $loan->id)->update([
                'disbursed_date' => $newDisbursedDate,
                'total_paid'     => $newTotalPaid,
                'outstanding'    => $newOutstanding,
            ]);

            $name = $loan->member?->name ?? "Loan #{$loan->id}";
            $this->command->info(sprintf(
                "  %-30s  cair=%s  paid=%d bln  → %s",
                $name,
                $newDisbursedDate,
                $s['paid'],
                $s['label'],
            ));
        }

        $this->command->info('');
        $this->command->info('CollectionOverdueSeeder done. Loan lainnya tetap Lancar.');
    }
}
