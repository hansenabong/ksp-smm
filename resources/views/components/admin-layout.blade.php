@php
    $u = auth()->user();

    // ── Role matrix (Finance/Accounting standard) ──────────────────────────────
    // SUPER_ADMIN      : everything + accounting structure
    // ADMIN            : master data + user mgmt + reporting view
    // MANAGER          : approve loans + monitoring (reports, accounting view)
    // BENDAHARA        : all transaction entry + approve/void + reports
    // PETUGAS          : entry transactions (kas, simpanan, loan payments, applications)
    // KASIR            : front-desk teller — cash, savings, loan payment entry
    // ADMIN_DATA       : member registration + loan application data entry
    // ADMIN_COLLECTION : loan monitoring + payment collection
    // ─────────────────────────────────────────────────────────────────────────

    // Kas / Simpanan entry — Bendahara, Petugas, Kasir
    $canCashTx    = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS', 'KASIR']);
    // Kas / Simpanan view (index) — same + PENGAWAS read-only
    $canCashView  = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS', 'KASIR', 'PENGAWAS']);
    $canSavings   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS', 'KASIR']);
    // Savings index view — same + PENGAWAS read-only
    $canSavView   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS', 'KASIR', 'PENGAWAS']);
    // Void — Bendahara supervisor sign-off only
    $canSavVoid   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA']);
    // Loan applications — Manager (approves) + Petugas/Admin_Data (enters) + PENGAWAS (views)
    $canLoanApp   = $u?->hasAnyRole(['SUPER_ADMIN', 'MANAGER', 'PETUGAS', 'ADMIN_DATA', 'PENGAWAS']);
    // Loan payments entry — Bendahara, Petugas, Kasir, Admin_Collection
    $canPayments  = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS', 'KASIR', 'ADMIN_COLLECTION']);
    // Payment void — Bendahara only
    $canPayVoid   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA']);
    // Reports — management + operational roles + PENGAWAS
    $canReports   = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA', 'KASIR', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'PENGAWAS']);
    $canAudit     = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA']);
    $canAcc       = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA']);
    // Penagihan — Admin_Collection + management + PENGAWAS (view-only)
    $canCollection = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'ADMIN_COLLECTION', 'PENGAWAS']);
    // Master data config — Admin + Super Admin + Admin_Data + PENGAWAS (view-only)
    $canMaster    = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'ADMIN_DATA', 'PENGAWAS']);
    // User management — Admin + Super Admin (route enforces PETUGAS-only for ADMIN)
    $canUsers     = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN']);

    $link = fn($active) => 'flex items-center px-3 py-1.5 rounded-md text-sm transition-colors ' .
        ($active
            ? 'bg-indigo-50 text-indigo-700 font-semibold'
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800');

    $section = 'px-3 pt-4 pb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest';
@endphp

<x-app-layout>
    <x-slot name="header">
        {{ $header ?? '' }}
    </x-slot>

    <div x-data="{ sidebarOpen: false }" class="py-6 relative">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-5">

                {{-- Backdrop (mobile) --}}
                <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
                    class="fixed inset-0 bg-black/30 z-40 md:hidden"></div>

                {{-- Sidebar --}}
                <aside
                    class="fixed inset-y-0 left-0 z-50 w-56 md:static md:z-auto md:w-56 flex-shrink-0
                           transform transition-transform duration-200
                           -translate-x-full md:translate-x-0"
                    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-y-auto
                                h-full md:h-auto md:sticky md:top-6">

                        {{-- Sidebar header --}}
                        <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Menu Admin</div>
                            @php $notifCount = \App\Http\Controllers\Admin\NotificationController::badgeCount($u); @endphp
                            @if($notifCount > 0)
                            <a href="{{ route('admin.notifications.index') }}"
                               class="relative inline-flex items-center text-gray-400 hover:text-indigo-600 transition" title="Notifikasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center w-3.5 h-3.5 bg-red-500 text-white rounded-full text-[9px] font-bold leading-none">
                                    {{ $notifCount }}
                                </span>
                            </a>
                            @endif
                        </div>

                        <nav class="px-2 py-2 space-y-0.5">

                            <div class="{{ $section }}">Utama</div>
                            <a class="{{ $link(request()->routeIs('admin.home')) }}"
                               href="{{ route('admin.home') }}">Admin Home</a>
                            <a class="{{ $link(request()->routeIs('admin.notifications.*')) }}"
                               href="{{ route('admin.notifications.index') }}">
                               Notifikasi
                               @if($notifCount > 0)
                               <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-600 leading-none">{{ $notifCount }}</span>
                               @endif
                            </a>

                            {{-- KAS --}}
                            <div class="{{ $section }}">Kas</div>
                            <a class="{{ $link(request()->routeIs('admin.cash.balances.*')) }}"
                               href="{{ route('admin.cash.balances.index') }}">Saldo Kas</a>
                            @if($canCashView)
                            <a class="{{ $link(request()->routeIs('admin.cash.income.*')) }}"
                               href="{{ route('admin.cash.income.index') }}">Pemasukan</a>
                            <a class="{{ $link(request()->routeIs('admin.cash.expense.*')) }}"
                               href="{{ route('admin.cash.expense.index') }}">Pengeluaran</a>
                            <a class="{{ $link(request()->routeIs('admin.cash.transfer.*')) }}"
                               href="{{ route('admin.cash.transfer.index') }}">Transfer</a>
                            @endif

                            {{-- SIMPANAN --}}
                            @if($canSavView || $u?->hasAnyRole(['ADMIN','MANAGER','ADMIN_DATA']))
                            <div class="{{ $section }}">Simpanan</div>
                            @if($canSavView)
                            <a class="{{ $link(request()->routeIs('admin.savings.deposit.*') && !request()->routeIs('admin.savings.bulk_wajib.*')) }}"
                               href="{{ route('admin.savings.deposit.index') }}">Setoran</a>
                            <a class="{{ $link(request()->routeIs('admin.savings.withdraw.*')) }}"
                               href="{{ route('admin.savings.withdraw.index') }}">Penarikan</a>
                            @if($canSavings)
                            <a class="{{ $link(request()->routeIs('admin.savings.bulk_wajib.*')) }}"
                               href="{{ route('admin.savings.bulk_wajib.create') }}">Simpanan Wajib Massal</a>
                            @endif
                            @endif
                            @if($u?->hasAnyRole(['SUPER_ADMIN','ADMIN','MANAGER','BENDAHARA','ADMIN_DATA','PENGAWAS']))
                            <a class="{{ $link(request()->routeIs('admin.savings.member_balance.*')) }}"
                               href="{{ route('admin.savings.member_balance.index') }}">Saldo Anggota</a>
                            @endif
                            @endif

                            {{-- PINJAMAN --}}
                            <div class="{{ $section }}">Pinjaman</div>
                            @if($canLoanApp)
                            <a class="{{ $link(request()->routeIs('admin.loans.applications.*')) }}"
                               href="{{ route('admin.loans.applications.index') }}">Pengajuan</a>
                            @endif
                            <a class="{{ $link(request()->routeIs('admin.loans.active.*') || (request()->routeIs('admin.loans.payments.*') && !request()->routeIs('admin.loans.payments.void.*'))) }}"
                               href="{{ route('admin.loans.active.index') }}">Pinjaman Aktif</a>

                            {{-- PENAGIHAN --}}
                            @if($canCollection)
                            <div class="{{ $section }}">Penagihan</div>
                            <a class="{{ $link(request()->routeIs('admin.collection.index') || (request()->routeIs('admin.collection.show') || request()->routeIs('admin.collection.update') || request()->routeIs('admin.collection.letters.*'))) }}"
                               href="{{ route('admin.collection.index') }}">Master Tagihan</a>
                            <a class="{{ $link(request()->routeIs('admin.collection.master-dhia')) }}"
                               href="{{ route('admin.collection.master-dhia') }}">Data Master DHIA</a>
                            <a class="{{ $link(request()->routeIs('admin.collection.laporan')) }}"
                               href="{{ route('admin.collection.laporan') }}">Laporan Collection</a>
                            @endif

                            {{-- LAPORAN --}}
                            @if($canReports)
                            <div class="{{ $section }}">Laporan</div>
                            <a class="{{ $link(request()->routeIs('admin.reports.cash')) }}"
                               href="{{ route('admin.reports.cash') }}">Kas</a>
                            <a class="{{ $link(request()->routeIs('admin.reports.savings')) }}"
                               href="{{ route('admin.reports.savings') }}">Simpanan</a>
                            <a class="{{ $link(request()->routeIs('admin.reports.loans')) }}"
                               href="{{ route('admin.reports.loans') }}">Pinjaman</a>
                            @endif

                            @if ($canMaster)
                                <div class="{{ $section }}">Master Data</div>
                                <a class="{{ $link(request()->routeIs('admin.master.cash_categories.*')) }}"
                                   href="{{ route('admin.master.cash_categories.index') }}">Kategori Kas</a>
                                <a class="{{ $link(request()->routeIs('admin.master.cash_accounts.*')) }}"
                                   href="{{ route('admin.master.cash_accounts.index') }}">Kas</a>
                                <a class="{{ $link(request()->routeIs('admin.master.members.*')) }}"
                                   href="{{ route('admin.master.members.index') }}">Anggota</a>
                                <a class="{{ $link(request()->routeIs('admin.master.saving_products.*')) }}"
                                   href="{{ route('admin.master.saving_products.index') }}">Jenis Simpanan</a>
                                <a class="{{ $link(request()->routeIs('admin.master.loan_products.*')) }}"
                                   href="{{ route('admin.master.loan_products.index') }}">Jenis Pinjaman</a>
                            @endif

                            @if ($canAudit)
                                <div class="{{ $section }}">Audit</div>
                                <a class="{{ $link(request()->routeIs('admin.audit.log.*')) }}"
                                   href="{{ route('admin.audit.log.index') }}">Log Aktivitas</a>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.cash')) }}"
                                   href="{{ route('admin.audit.void.cash') }}">VOID Kas</a>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.savings')) }}"
                                   href="{{ route('admin.audit.void.savings') }}">VOID Simpanan</a>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.loan_payments')) }}"
                                   href="{{ route('admin.audit.void.loan_payments') }}">VOID Angsuran</a>
                            @endif

                            @if ($canAcc)
                                <div class="{{ $section }}">Akuntansi</div>
                                @if($u?->hasRole('SUPER_ADMIN'))
                                <a class="{{ $link(request()->routeIs('admin.accounting.accounts.*')) }}"
                                   href="{{ route('admin.accounting.accounts.index') }}">Bagan Akun (COA)</a>
                                @endif
                                <a class="{{ $link(request()->routeIs('admin.accounting.income_statement.*')) }}"
                                   href="{{ route('admin.accounting.income_statement.index') }}">Laba Rugi</a>
                                <a class="{{ $link(request()->routeIs('admin.accounting.balance_sheet.*')) }}"
                                   href="{{ route('admin.accounting.balance_sheet.index') }}">Neraca</a>
                                <a class="{{ $link(request()->routeIs('admin.accounting.trial_balance.*')) }}"
                                   href="{{ route('admin.accounting.trial_balance.index') }}">Trial Balance</a>
                                <a class="{{ $link(request()->routeIs('admin.accounting.general_ledger.*')) }}"
                                   href="{{ route('admin.accounting.general_ledger.index') }}">Buku Besar</a>
                                <a class="{{ $link(request()->routeIs('admin.accounting.journal_entries.*')) }}"
                                   href="{{ route('admin.accounting.journal_entries.index') }}">Jurnal</a>
                                <a class="{{ $link(request()->routeIs('admin.shu.*')) }}"
                                   href="{{ route('admin.shu.index') }}">SHU</a>
                            @endif

                            @if ($canUsers)
                                <div class="{{ $section }}">Pengguna</div>
                                <a class="{{ $link(request()->routeIs('admin.users.*')) }}"
                                   href="{{ route('admin.users.index') }}">Manajemen User</a>
                            @endif

                            {{-- Akun -- semua pengguna --}}
                            <div class="{{ $section }}">Akun Saya</div>
                            <a class="{{ $link(request()->routeIs('admin.password.change')) }}"
                               href="{{ route('admin.password.change') }}">Ganti Password</a>

                        </nav>

                        <div class="h-4"></div>
                    </div>
                </aside>

                {{-- Main content --}}
                <main class="flex-1 min-w-0 space-y-0">
                    {{-- Mobile menu toggle --}}
                    <div class="md:hidden mb-4">
                        <button @click="sidebarOpen = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 text-white text-sm rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            Menu
                        </button>
                    </div>

                    {{ $slot }}
                </main>

            </div>
        </div>
    </div>
</x-app-layout>
