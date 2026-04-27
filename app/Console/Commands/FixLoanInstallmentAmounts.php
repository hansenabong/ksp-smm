<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixLoanInstallmentAmounts extends Command
{
    protected $signature = 'loans:fix-installment-amounts
                            {--execute : Apply changes (omit for dry run)}';

    protected $description = 'Fix monthly_installment / total_payable / outstanding for loans where admin_monthly was baked into monthly_installment before it was dropped.';

    public function handle(): int
    {
        $execute = $this->option('execute');

        if (! $execute) {
            $this->warn('DRY RUN — pass --execute to apply changes.');
            $this->newLine();
        }

        $loans = Loan::where('status', 'ACTIVE')
            ->orWhere('status', 'CLOSED')
            ->get(['id','principal','tenor_months','principal_monthly','interest_monthly',
                   'insurance_monthly','monthly_installment','total_payable','total_paid','outstanding']);

        $affected = [];

        foreach ($loans as $loan) {
            $correctInstallment = (int) $loan->principal_monthly
                                + (int) $loan->interest_monthly
                                + (int) $loan->insurance_monthly;

            if ($loan->monthly_installment === $correctInstallment) {
                continue; // already correct
            }

            $tenor        = (int) $loan->tenor_months;
            $principal    = (int) $loan->principal;
            $pm           = (int) $loan->principal_monthly;
            $im           = (int) $loan->interest_monthly;
            $insm         = (int) $loan->insurance_monthly;
            $lastPrincipal = $principal - $pm * ($tenor - 1);

            $correctTotalPayable = ($correctInstallment * ($tenor - 1))
                                 + ($lastPrincipal + $im + $insm);

            $correctOutstanding  = $correctTotalPayable - (int) $loan->total_paid;

            $affected[] = [
                'loan'                => $loan,
                'correctInstallment'  => $correctInstallment,
                'correctTotalPayable' => $correctTotalPayable,
                'correctOutstanding'  => $correctOutstanding,
            ];
        }

        if (empty($affected)) {
            $this->info('No affected loans found — all monthly_installment values are correct.');
            return 0;
        }

        $this->table(
            ['Loan ID', 'Old Installment', 'New Installment', 'Old Total Payable', 'New Total Payable', 'Old Outstanding', 'New Outstanding'],
            array_map(fn($r) => [
                $r['loan']->id,
                number_format($r['loan']->monthly_installment),
                number_format($r['correctInstallment']),
                number_format($r['loan']->total_payable),
                number_format($r['correctTotalPayable']),
                number_format($r['loan']->outstanding),
                number_format($r['correctOutstanding']),
            ], $affected)
        );

        $this->newLine();
        $this->line(count($affected) . ' loan(s) would be updated.');

        if (! $execute) {
            $this->warn('Run with --execute to apply.');
            return 0;
        }

        if (! $this->confirm('Apply these changes to the database?')) {
            $this->info('Aborted.');
            return 0;
        }

        DB::transaction(function () use ($affected) {
            foreach ($affected as $row) {
                $row['loan']->update([
                    'monthly_installment' => $row['correctInstallment'],
                    'total_payable'       => $row['correctTotalPayable'],
                    'outstanding'         => max(0, $row['correctOutstanding']),
                ]);
                $this->line("  ✓ Loan #{$row['loan']->id} updated.");
            }
        });

        $this->info('Done.');
        return 0;
    }
}
