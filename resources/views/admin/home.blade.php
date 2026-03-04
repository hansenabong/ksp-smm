<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <div class="text-sm text-gray-400">
                {{ auth()->user()->name ?? auth()->user()->email }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">

        @if (session('success'))
            <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Welcome --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-5">
            <div class="text-lg font-semibold text-gray-800">Selamat datang, {{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="text-sm text-gray-400 mt-0.5">Pilih modul di bawah atau gunakan menu samping untuk mulai bekerja.</div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- KAS --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Kas</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.cash.balances.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Saldo</a>
                    <a href="{{ route('admin.cash.income.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Pemasukan</a>
                    <a href="{{ route('admin.cash.expense.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Pengeluaran</a>
                    <a href="{{ route('admin.cash.transfer.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Transfer</a>
                </div>
            </div>

            {{-- SIMPANAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Simpanan</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.savings.deposit.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Setoran</a>
                    <a href="{{ route('admin.savings.withdraw.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Penarikan</a>
                </div>
            </div>

            {{-- PINJAMAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Pinjaman</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.loans.applications.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Pengajuan</a>
                    <a href="{{ route('admin.loans.active.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Pinjaman Aktif</a>
                    <a href="{{ route('admin.reports.loans') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Laporan</a>
                </div>
            </div>

            {{-- LAPORAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Laporan</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.reports.cash') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Kas</a>
                    <a href="{{ route('admin.reports.savings') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Simpanan</a>
                    <a href="{{ route('admin.reports.loans') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Pinjaman</a>
                </div>
            </div>

            {{-- MASTER DATA --}}
            @if(auth()->user()?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Master Data</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.master.cash_accounts.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Kas</a>
                    <a href="{{ route('admin.master.members.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Anggota</a>
                    <a href="{{ route('admin.master.saving_products.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Jenis Simpanan</a>
                    <a href="{{ route('admin.master.loan_products.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Jenis Pinjaman</a>
                </div>
            </div>
            @endif

            {{-- AUDIT --}}
            @if(auth()->user()?->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Audit VOID</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.audit.void.cash') }}"
                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition">Kas</a>
                    <a href="{{ route('admin.audit.void.savings') }}"
                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition">Simpanan</a>
                    <a href="{{ route('admin.audit.void.loan_payments') }}"
                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition">Angsuran</a>
                </div>
            </div>

            {{-- AKUNTANSI --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-gray-800">Akuntansi</div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.accounting.trial_balance.index') }}"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-medium transition">Trial Balance</a>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-admin-layout>
