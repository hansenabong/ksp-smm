<?php

namespace Database\Seeders;

use App\Models\Loan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Simulates overdue loans for demo/testing by reducing total_paid
 * on some active loans. Run AFTER DemoTransactionSeeder.
 *
 * Usage: php artisan db:seed --class=CollectionOverdueSeeder
 */
class CollectionOverdueSeeder extends Seeder
{
    public function run(): void
    {
        $loans = Loan::with('member')
            ->where('status', 'ACTIVE')
            ->orderBy('disbursed_date')
            ->get();

        if ($loans->count() < 6) {
            $this->command->warn('Need at least 6 active loans. Run DemoTransactionSeeder first.');
            return;
        }

        // [loan_index => months_overdue]  (index 0 = oldest disbursed)
        $scenarios = [
            0 => 1,   // 1 bulan nunggak
            1 => 2,   // 2 bulan
            2 => 3,   // 3 bulan
            3 => 4,   // 4 bulan
            4 => 5,   // 5 bulan
            5 => 6,   // 6 bulan
        ];

        foreach ($scenarios as $idx => $monthsOverdue) {
            /** @var Loan $loan */
            $loan = $loans->get($idx);
            if (!$loan || $loan->monthly_installment <= 0) {
                continue;
            }

            $currentPaidMonths = (int) floor($loan->total_paid / $loan->monthly_installment);
            $newPaidMonths     = max(0, $currentPaidMonths - $monthsOverdue);
            $newTotalPaid      = (int) ($newPaidMonths * $loan->monthly_installment);
            $newOutstanding    = max(0, (int) $loan->total_payable - $newTotalPaid);

            DB::table('loans')->where('id', $loan->id)->update([
                'total_paid'  => $newTotalPaid,
                'outstanding' => $newOutstanding,
            ]);

            $name = $loan->member?->name ?? "Loan #{$loan->id}";
            $this->command->info(sprintf(
                "  %-30s  -%d bln → paid=%d, outstanding=%d",
                $name,
                $monthsOverdue,
                $newTotalPaid,
                $newOutstanding,
            ));
        }

        $this->command->info('CollectionOverdueSeeder done. Sisa loan lainnya tetap Lancar.');
    }
}
