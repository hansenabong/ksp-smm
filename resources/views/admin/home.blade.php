<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
                <div class="text-xs text-gray-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
            <div class="text-sm text-gray-500">{{ auth()->user()->name ?? auth()->user()->email }}</div>
        </div>
    </x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Pending applications alert --}}
        @if($pendingApplicationsCount > 0)
            <div class="flex items-center justify-between px-4 py-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span><strong>{{ $pendingApplicationsCount }} pengajuan pinjaman</strong> menunggu persetujuan.</span>
                </div>
                <a href="{{ route('admin.loans.applications.index') }}"
                   class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2">
                    Lihat Semua →
                </a>
            </div>
        @endif

        {{-- Main KPI cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota Aktif</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($totalActiveMembers, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">anggota terdaftar &amp; aktif</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Saldo Kas</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalCashBalance, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">semua rekening kas &amp; bank</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Simpanan</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalSavings, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">kewajiban simpanan anggota</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Piutang Pinjaman</span>
                    <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="text-2xl font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $activeLoanCount }} pinjaman aktif</div>
            </div>

        </div>

        {{-- Secondary KPI row --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pendapatan Bulan Ini</div>
                <div class="text-lg font-bold text-green-700 tabular-nums">+ Rp {{ number_format($monthIncome, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $currentMonth }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pengeluaran Bulan Ini</div>
                <div class="text-lg font-bold text-red-700 tabular-nums">− Rp {{ number_format($monthExpense, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $currentMonth }}</div>
            </div>

            <a href="{{ route('admin.loans.active.index') }}"
               class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 hover:bg-gray-50 transition block">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pinjaman Aktif</div>
                <div class="text-lg font-bold text-gray-800">{{ $activeLoanCount }} pinjaman</div>
                <div class="text-xs text-gray-400 mt-0.5">klik untuk lihat daftar</div>
            </a>

            <a href="{{ route('admin.loans.applications.index') }}"
               class="rounded-xl shadow-sm border px-5 py-4 transition block
                      {{ $pendingApplicationsCount > 0 ? 'bg-amber-50 border-amber-200 hover:bg-amber-100' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                <div class="text-xs font-semibold uppercase tracking-wide mb-2
                            {{ $pendingApplicationsCount > 0 ? 'text-amber-700' : 'text-gray-500' }}">
                    Pengajuan Pending
                </div>
                <div class="text-lg font-bold {{ $pendingApplicationsCount > 0 ? 'text-amber-800' : 'text-gray-800' }}">
                    {{ $pendingApplicationsCount }} pengajuan
                </div>
                <div class="text-xs mt-0.5 {{ $pendingApplicationsCount > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                    {{ $pendingApplicationsCount > 0 ? 'perlu tindakan' : 'tidak ada antrian' }}
                </div>
            </a>

        </div>

        {{-- Cash balances + Recent transactions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Saldo Rekening Kas</span>
                    <a href="{{ route('admin.cash.balances.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Detail →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($cashBalances as $account)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[9px] font-bold shrink-0
                                    {{ $account->type === 'BANK' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600' }}">
                                    {{ $account->type === 'BANK' ? 'B' : 'K' }}
                                </span>
                                <span class="text-sm text-gray-700">{{ $account->name }}</span>
                            </div>
                            <span class="text-sm font-semibold tabular-nums {{ $account->balance < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                Rp {{ number_format($account->balance, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-sm text-center text-gray-400">Belum ada rekening kas.</div>
                    @endforelse
                </div>
                @if($cashBalances->count() > 0)
                    <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total</span>
                        <span class="text-sm font-bold text-gray-900 tabular-nums">Rp {{ number_format($totalCashBalance, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Aktivitas Kas Terbaru</span>
                    <a href="{{ route('admin.reports.cash') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Semua →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($recentTransactions as $tx)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <span class="shrink-0 w-14 text-center text-[10px] font-bold px-1 py-0.5 rounded
                                {{ $tx->type === 'INCOME' ? 'bg-green-100 text-green-700' : ($tx->type === 'EXPENSE' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $tx->type === 'INCOME' ? 'MASUK' : ($tx->type === 'EXPENSE' ? 'KELUAR' : 'TRANSF') }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-gray-700 truncate">
                                    @if($tx->type === 'INCOME')
                                        {{ $tx->toCashAccount?->name ?? '—' }}
                                    @elseif($tx->type === 'EXPENSE')
                                        {{ $tx->fromCashAccount?->name ?? '—' }}
                                    @else
                                        {{ $tx->fromCashAccount?->name ?? '—' }} → {{ $tx->toCashAccount?->name ?? '—' }}
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($tx->trx_date)->translatedFormat('d M Y') }}</div>
                            </div>
                            <span class="text-xs font-semibold tabular-nums shrink-0
                                {{ $tx->type === 'INCOME' ? 'text-green-700' : ($tx->type === 'EXPENSE' ? 'text-red-700' : 'text-blue-700') }}">
                                {{ $tx->type === 'EXPENSE' ? '−' : '+' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-sm text-center text-gray-400">Belum ada transaksi kas.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Pending applications table --}}
        @if($pendingApplications->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-amber-100 bg-amber-50 flex items-center justify-between">
                    <span class="text-sm font-semibold text-amber-800">Pengajuan Pinjaman Menunggu Persetujuan</span>
                    <a href="{{ route('admin.loans.applications.index') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-900">Lihat Semua →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Pokok</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="px-4 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($pendingApplications as $app)
                                <tr class="hover:bg-amber-50 transition">
                                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ $app->member?->name ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-gray-600">{{ $app->loanProduct?->name ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-gray-800">Rp {{ number_format($app->principal, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($app->trx_date)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="{{ route('admin.loans.applications.show', $app) }}"
                                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Tinjau →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-admin-layout>
