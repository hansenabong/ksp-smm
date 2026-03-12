<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\ShuPeriod;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $alerts = self::buildAlerts(auth()->user());
        return view('admin.notifications.index', compact('alerts'));
    }

    /**
     * Build role-filtered alert list.
     * Each item: [level, icon, title, message, url, label]
     */
    public static function buildAlerts($u): array
    {
        $alerts = [];

        // Pending loan applications — SUPER_ADMIN
        if ($u?->hasRole('SUPER_ADMIN')) {
            $count = LoanApplication::where('status', 'PENDING')->count();
            if ($count > 0) {
                $alerts[] = [
                    'level'   => 'warning',
                    'icon'    => 'clock',
                    'title'   => 'Pengajuan Pinjaman Menunggu Persetujuan',
                    'message' => "{$count} pengajuan pinjaman belum diproses.",
                    'url'     => route('admin.loans.applications.index'),
                    'label'   => 'Lihat Pengajuan',
                ];
            }
        }

        // Overdue active loans (no payment in last 30 days) — treasury / collection
        if ($u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN_COLLECTION'])) {
            $cutoff = Carbon::today()->subDays(30)->toDateString();
            $overdueLoans = Loan::where('status', 'ACTIVE')
                ->where('outstanding', '>', 0)
                ->whereDoesntHave('payments', fn($q) => $q->where('is_voided', false)->where('trx_date', '>=', $cutoff))
                ->count();

            if ($overdueLoans > 0) {
                $alerts[] = [
                    'level'   => 'danger',
                    'icon'    => 'exclamation',
                    'title'   => 'Pinjaman Menunggak',
                    'message' => "{$overdueLoans} pinjaman aktif tidak ada angsuran dalam 30 hari terakhir.",
                    'url'     => route('admin.loans.active.index'),
                    'label'   => 'Lihat Pinjaman Aktif',
                ];
            }
        }

        // Draft SHU periods — SUPER_ADMIN / FINANCE
        if ($u?->hasAnyRole(['SUPER_ADMIN', 'FINANCE'])) {
            $draftShu = ShuPeriod::where('status', 'DRAFT')->count();
            if ($draftShu > 0) {
                $alerts[] = [
                    'level'   => 'info',
                    'icon'    => 'calculator',
                    'title'   => 'SHU Draft Menunggu Finalisasi',
                    'message' => "{$draftShu} periode SHU masih berstatus DRAFT.",
                    'url'     => route('admin.shu.index'),
                    'label'   => 'Buka SHU',
                ];
            }
        }

        // Loans nearing end of tenor — treasury / collection
        if ($u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN_COLLECTION'])) {
            $nearMaturity = Loan::where('status', 'ACTIVE')
                ->where('outstanding', '>', 0)
                ->whereRaw("disbursed_date <= (CURRENT_DATE - (tenor_months - 1) * INTERVAL '1 month')")
                ->count();

            if ($nearMaturity > 0) {
                $alerts[] = [
                    'level'   => 'warning',
                    'icon'    => 'calendar',
                    'title'   => 'Pinjaman Mendekati Jatuh Tempo',
                    'message' => "{$nearMaturity} pinjaman aktif mendekati akhir tenor.",
                    'url'     => route('admin.loans.active.index'),
                    'label'   => 'Lihat Pinjaman',
                ];
            }
        }

        return $alerts;
    }

    /** Total count for sidebar badge. */
    public static function badgeCount($u): int
    {
        return count(static::buildAlerts($u));
    }
}
