@php
    $routeName = request()->route()?->getName() ?? '';

    // ── Breadcrumb mapping: route prefix → [section, page] ──────────────
    $crumbs = [];

    if ($routeName === 'admin.home') {
        // No breadcrumbs on home page
    } elseif (str_starts_with($routeName, 'admin.notifications')) {
        $crumbs = [['Notifikasi', null]];
    }
    // ── Kas ──
    elseif (str_starts_with($routeName, 'admin.cash.balances')) {
        $crumbs = [['Kas', null], ['Saldo Kas', null]];
    } elseif (str_starts_with($routeName, 'admin.cash.income')) {
        $crumbs = [['Kas', null], ['Pemasukan', null]];
    } elseif (str_starts_with($routeName, 'admin.cash.expense')) {
        $crumbs = [['Kas', null], ['Pengeluaran', null]];
    } elseif (str_starts_with($routeName, 'admin.cash.transfer')) {
        $crumbs = [['Kas', null], ['Transfer', null]];
    }
    // ── Simpanan ──
    elseif (str_starts_with($routeName, 'admin.savings.deposit')) {
        $crumbs = [['Simpanan', null], ['Setoran', null]];
    } elseif (str_starts_with($routeName, 'admin.savings.withdraw')) {
        $crumbs = [['Simpanan', null], ['Penarikan', null]];
    } elseif (str_starts_with($routeName, 'admin.savings.member_balance')) {
        $crumbs = [['Simpanan', null], ['Saldo Anggota', null]];
    } elseif (str_starts_with($routeName, 'admin.savings.bulk_wajib')) {
        $crumbs = [['Simpanan', null], ['Bulk Simpanan Wajib', null]];
    }
    // ── Pinjaman ──
    elseif (str_starts_with($routeName, 'admin.loans.applications')) {
        $page = str_contains($routeName, 'create') ? 'Buat Pengajuan' :
               (str_contains($routeName, 'edit') ? 'Edit Pengajuan' :
               (str_contains($routeName, 'show') ? 'Detail Pengajuan' : 'Pengajuan'));
        $crumbs = [['Pinjaman', null], [$page, null]];
    } elseif (str_starts_with($routeName, 'admin.loans.payments')) {
        $crumbs = [['Pinjaman', null], ['Bayar Angsuran', null]];
    } elseif (str_starts_with($routeName, 'admin.loans.active')) {
        $page = str_contains($routeName, 'show') ? 'Detail Pinjaman' : 'Pinjaman Approved';
        $crumbs = [['Pinjaman', null], [$page, null]];
    }
    // ── Collection ──
    elseif (str_starts_with($routeName, 'admin.collection.master-dhia')) {
        $crumbs = [['Penagihan', null], ['Data Master DHIA', null]];
    } elseif (str_starts_with($routeName, 'admin.collection.laporan')) {
        $crumbs = [['Penagihan', null], ['Laporan Collection', null]];
    } elseif (str_starts_with($routeName, 'admin.collection.letters')) {
        $crumbs = [['Penagihan', null], ['Surat Penagihan', null]];
    } elseif (str_starts_with($routeName, 'admin.collection.schedule')) {
        $crumbs = [['Penagihan', null], ['Jadwal Angsuran', null]];
    } elseif (str_starts_with($routeName, 'admin.collection.settlement')) {
        $crumbs = [['Penagihan', null], ['Pelunasan', null]];
    } elseif (str_starts_with($routeName, 'admin.collection.subledger')) {
        $crumbs = [['Penagihan', null], ['Sub Ledger', null]];
    } elseif (str_starts_with($routeName, 'admin.collection')) {
        $page = str_contains($routeName, 'show') ? 'Detail Tagihan' : 'Master Tagihan';
        $crumbs = [['Penagihan', null], [$page, null]];
    }
    // ── Laporan ──
    elseif ($routeName === 'admin.reports.cash') {
        $crumbs = [['Laporan', null], ['Kas', null]];
    } elseif ($routeName === 'admin.reports.savings') {
        $crumbs = [['Laporan', null], ['Simpanan', null]];
    } elseif ($routeName === 'admin.reports.loans') {
        $crumbs = [['Laporan', null], ['Pinjaman', null]];
    }
    // ── Master Data ──
    elseif (str_starts_with($routeName, 'admin.master.cash_categories')) {
        $crumbs = [['Master Data', null], ['Kategori Kas', null]];
    } elseif (str_starts_with($routeName, 'admin.master.cash_accounts')) {
        $crumbs = [['Master Data', null], ['Kas', null]];
    } elseif (str_starts_with($routeName, 'admin.master.members')) {
        $page = str_contains($routeName, 'create') ? 'Tambah Anggota' :
               (str_contains($routeName, 'edit') ? 'Edit Anggota' :
               (str_contains($routeName, 'show') ? 'Detail Anggota' : 'Anggota'));
        $crumbs = [['Master Data', null], [$page, null]];
    } elseif (str_starts_with($routeName, 'admin.master.saving_products')) {
        $crumbs = [['Master Data', null], ['Jenis Simpanan', null]];
    } elseif (str_starts_with($routeName, 'admin.master.loan_products')) {
        $crumbs = [['Master Data', null], ['Jenis Pinjaman', null]];
    }
    // ── Legal ──
    elseif (str_starts_with($routeName, 'admin.legal')) {
        $page = str_contains($routeName, 'create') ? 'Buat Berkas' :
               (str_contains($routeName, 'show') ? 'Detail Berkas' : 'Dokumen Legal');
        $crumbs = [['Legal', null], [$page, null]];
    }
    // ── Audit ──
    elseif (str_starts_with($routeName, 'admin.audit.log')) {
        $crumbs = [['Audit', null], ['Log Aktivitas', null]];
    } elseif ($routeName === 'admin.audit.void.cash') {
        $crumbs = [['Audit', null], ['VOID Kas', null]];
    } elseif ($routeName === 'admin.audit.void.savings') {
        $crumbs = [['Audit', null], ['VOID Simpanan', null]];
    } elseif ($routeName === 'admin.audit.void.loan_payments') {
        $crumbs = [['Audit', null], ['VOID Angsuran', null]];
    }
    // ── Akuntansi ──
    elseif (str_starts_with($routeName, 'admin.accounting.accounts')) {
        $crumbs = [['Akuntansi', null], ['Bagan Akun (COA)', null]];
    } elseif (str_starts_with($routeName, 'admin.accounting.income_statement')) {
        $crumbs = [['Akuntansi', null], ['Laba Rugi', null]];
    } elseif (str_starts_with($routeName, 'admin.accounting.balance_sheet')) {
        $crumbs = [['Akuntansi', null], ['Neraca', null]];
    } elseif (str_starts_with($routeName, 'admin.accounting.trial_balance')) {
        $crumbs = [['Akuntansi', null], ['Trial Balance', null]];
    } elseif (str_starts_with($routeName, 'admin.accounting.general_ledger')) {
        $crumbs = [['Akuntansi', null], ['Buku Besar', null]];
    } elseif (str_starts_with($routeName, 'admin.accounting.journal_entries')) {
        $crumbs = [['Akuntansi', null], ['Jurnal', null]];
    } elseif (str_starts_with($routeName, 'admin.shu')) {
        $crumbs = [['Akuntansi', null], ['SHU', null]];
    }
    // ── Pengguna ──
    elseif (str_starts_with($routeName, 'admin.users')) {
        $page = str_contains($routeName, 'create') ? 'Tambah User' :
               (str_contains($routeName, 'edit') ? 'Edit User' : 'Manajemen User');
        $crumbs = [['Pengguna', null], [$page, null]];
    }
    // ── Akun ──
    elseif (str_starts_with($routeName, 'admin.password')) {
        $crumbs = [['Ganti Password', null]];
    }
    // ── Collateral ──
    elseif (str_starts_with($routeName, 'admin.collaterals')) {
        $crumbs = [['Pinjaman', null], ['Agunan', null]];
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        {{ $header ?? '' }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">

            {{-- Breadcrumbs --}}
            @if(count($crumbs) > 0)
            <nav class="mb-4 flex items-center gap-1.5 text-sm print:hidden">
                <a href="{{ route('admin.home') }}"
                   class="inline-flex items-center gap-1 text-gray-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Home</span>
                </a>

                @foreach($crumbs as $i => $crumb)
                    <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>

                    @if($i === count($crumbs) - 1)
                        {{-- Last crumb: current page (not a link) --}}
                        <span class="text-gray-700 font-medium">{{ $crumb[0] }}</span>
                    @else
                        {{-- Intermediate crumbs --}}
                        <span class="text-gray-400">{{ $crumb[0] }}</span>
                    @endif
                @endforeach
            </nav>
            @endif

            <main class="space-y-0">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-app-layout>
