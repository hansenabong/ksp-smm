@php
    $u = auth()->user();

    // ── Role matrix (Finance/Accounting standard) ──────────────────────────────
    // SUPER_ADMIN : everything + accounting structure
    // ADMIN       : master data + user mgmt (PETUGAS only) + reporting view
    // MANAGER     : approve loans + monitoring (reports, accounting view)
    // BENDAHARA   : all transaction entry + approve/void + reports
    // PETUGAS     : entry transactions (kas, simpanan, loan payments, applications)
    // ─────────────────────────────────────────────────────────────────────────

    // Kas / Simpanan entry — Bendahara & Petugas
    $canCashTx    = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS']);
    $canSavings   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS']);
    // Void — Bendahara supervisor sign-off only
    $canSavVoid   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA']);
    // Loan applications — Manager (approves) + Petugas (enters)
    $canLoanApp   = $u?->hasAnyRole(['SUPER_ADMIN', 'MANAGER', 'PETUGAS']);
    // Loan payments entry — Bendahara + Petugas
    $canPayments  = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'PETUGAS']);
    // Payment void — Bendahara only
    $canPayVoid   = $u?->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA']);
    // Reports / Audit / Accounting — everyone except Petugas
    $canReports   = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA']);
    $canAudit     = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA']);
    $canAcc       = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA']);
    // Master data config — Admin + Super Admin
    $canMaster    = $u?->hasAnyRole(['SUPER_ADMIN', 'ADMIN']);
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
                        <div class="px-4 py-3.5 border-b border-gray-100">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Menu Admin</div>
                        </div>

                        <nav class="px-2 py-2 space-y-0.5">

                            <div class="{{ $section }}">Utama</div>
                            <a class="{{ $link(request()->routeIs('admin.home')) }}"
                               href="{{ route('admin.home') }}">Admin Home</a>

                            {{-- KAS --}}
                            <div class="{{ $section }}">Kas</div>
                            <a class="{{ $link(request()->routeIs('admin.cash.balances.*')) }}"
                               href="{{ route('admin.cash.balances.index') }}">Saldo Kas</a>
                            @if($canCashTx)
                            <a class="{{ $link(request()->routeIs('admin.cash.income.*')) }}"
                               href="{{ route('admin.cash.income.index') }}">Pemasukan</a>
                            <a class="{{ $link(request()->routeIs('admin.cash.expense.*')) }}"
                               href="{{ route('admin.cash.expense.index') }}">Pengeluaran</a>
                            <a class="{{ $link(request()->routeIs('admin.cash.transfer.*')) }}"
                               href="{{ route('admin.cash.transfer.index') }}">Transfer</a>
                            @endif

                            {{-- SIMPANAN --}}
                            @if($canSavings)
                            <div class="{{ $section }}">Simpanan</div>
                            <a class="{{ $link(request()->routeIs('admin.savings.deposit.*')) }}"
                               href="{{ route('admin.savings.deposit.index') }}">Setoran</a>
                            <a class="{{ $link(request()->routeIs('admin.savings.withdraw.*')) }}"
                               href="{{ route('admin.savings.withdraw.index') }}">Penarikan</a>
                            @endif

                            {{-- PINJAMAN --}}
                            <div class="{{ $section }}">Pinjaman</div>
                            @if($canLoanApp)
                            <a class="{{ $link(request()->routeIs('admin.loans.applications.*')) }}"
                               href="{{ route('admin.loans.applications.index') }}">Pengajuan</a>
                            @endif
                            <a class="{{ $link(request()->routeIs('admin.loans.active.*') || (request()->routeIs('admin.loans.payments.*') && !request()->routeIs('admin.loans.payments.void.*'))) }}"
                               href="{{ route('admin.loans.active.index') }}">Pinjaman Aktif</a>

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
                                <div class="{{ $section }}">Audit VOID</div>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.cash')) }}"
                                   href="{{ route('admin.audit.void.cash') }}">Kas</a>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.savings')) }}"
                                   href="{{ route('admin.audit.void.savings') }}">Simpanan</a>
                                <a class="{{ $link(request()->routeIs('admin.audit.void.loan_payments')) }}"
                                   href="{{ route('admin.audit.void.loan_payments') }}">Angsuran</a>
                            @endif

                            @if ($canAcc)
                                <div class="{{ $section }}">Akuntansi</div>
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
