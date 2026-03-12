<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            @php $isCollection = request()->routeIs('admin.collection.*'); @endphp
            <div>
                <div class="text-xs text-gray-400 mb-0.5">{{ $isCollection ? 'Penagihan / Perhitungan Pelunasan' : 'Pinjaman / Aktif / Perhitungan Pelunasan' }}</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perhitungan Pelunasan — #{{ $loan->id }}</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route($isCollection ? 'admin.collection.settlement.pdf' : 'admin.loans.active.settlement.pdf', [$loan, 'settlement_date' => $settlementDate]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak PDF
                </a>
                <a href="{{ $isCollection ? route('admin.collection.show', $loan) : route('admin.loans.active.show', $loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Loan summary strip --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Anggota</div>
                    <div class="font-semibold text-gray-800">{{ $loan->member?->name ?? '-' }}</div>
                    <div class="text-xs text-gray-500">No. {{ $loan->member?->member_no ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Produk</div>
                    <div class="font-semibold text-gray-800">{{ $loan->loanProduct?->name ?? '-' }}</div>
                    <div class="text-xs text-gray-500">Tenor {{ $loan->tenor_months }} bulan</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Pokok Pinjaman</div>
                    <div class="font-semibold text-gray-800">Rp {{ number_format($loan->principal, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-500">Cair: {{ $loan->disbursed_date?->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Angsuran Ke- / Total</div>
                    <div class="font-semibold text-gray-800">{{ $calc['paid_count'] }} / {{ $loan->tenor_months }}</div>
                    <div class="text-xs text-gray-500">Sisa {{ $calc['remaining_periods'] }} angsuran</div>
                </div>
            </div>
        </div>

        {{-- Date picker --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
            <form method="GET" action="{{ route($isCollection ? 'admin.collection.settlement' : 'admin.loans.active.settlement', $loan) }}" class="flex items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tanggal Pelunasan
                    </label>
                    <input type="date" name="settlement_date"
                           value="{{ $settlementDate }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    Hitung
                </button>
            </form>
        </div>

        {{-- Calculation breakdown --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Rincian Pelunasan per {{ \Carbon\Carbon::parse($settlementDate)->format('d M Y') }}</h3>
            </div>

            <div class="p-6">
                <div class="max-w-lg">

                    {{-- Sisa kewajiban --}}
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Sisa Kewajiban Pokok</div>
                    <table class="w-full text-sm mb-4">
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-600">Sisa Tagihan (Outstanding)</td>
                            <td class="py-1.5 text-right font-mono font-medium text-gray-800">Rp {{ number_format($calc['outstanding'], 0, ',', '.') }}</td>
                        </tr>
                    </table>

                    {{-- Tunggakan --}}
                    @if($calc['overdue_count'] > 0)
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Tunggakan ({{ $calc['overdue_count'] }} angsuran)</div>
                    <table class="w-full text-sm mb-4">
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-600 pl-3">Angsuran Tertunggak</td>
                            <td class="py-1.5 text-right font-mono text-red-700">Rp {{ number_format($calc['tunggakan'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    @endif

                    {{-- Denda keterlambatan --}}
                    @if($calc['denda_keterlambatan'] > 0)
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Denda Keterlambatan ({{ number_format($calc['penalty_daily_rate_bp'] / 100, 2) }}%/hari)
                    </div>
                    <table class="w-full text-sm mb-4">
                        @foreach($calc['denda_details'] as $dd)
                        <tr class="border-b border-gray-50">
                            <td class="py-1 text-gray-500 pl-3 text-xs">
                                Angsuran #{{ $dd['installment_no'] }} — {{ $dd['due_date']->format('d M Y') }}
                                ({{ $dd['days_late'] }} hari)
                            </td>
                            <td class="py-1 text-right font-mono text-xs text-red-600">Rp {{ number_format($dd['penalty'], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-600 pl-3 font-medium">Total Denda Keterlambatan</td>
                            <td class="py-1.5 text-right font-mono font-semibold text-red-700">Rp {{ number_format($calc['denda_keterlambatan'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    @endif

                    {{-- Biaya pelunasan dipercepat --}}
                    @if($calc['is_early_repayment'])
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Pelunasan Dipercepat (sisa {{ $calc['remaining_periods'] }} angsuran)
                    </div>
                    <table class="w-full text-sm mb-4">
                        <tr class="border-b border-gray-100">
                            <td class="py-1.5 text-gray-600 pl-3">
                                Biaya Pelunasan Dipercepat
                                <span class="text-xs text-gray-400">({{ number_format($calc['early_repayment_rate_bp'] / 100, 2) }}% × sisa pokok)</span>
                            </td>
                            <td class="py-1.5 text-right font-mono text-orange-700">Rp {{ number_format($calc['biaya_pelunasan_dipercepat'], 0, ',', '.') }}</td>
                        </tr>
                    </table>
                    @endif

                    {{-- Total --}}
                    <div class="bg-indigo-50 rounded-lg p-4 mt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-indigo-500 font-semibold uppercase tracking-wide mb-0.5">Total Jumlah Pelunasan</div>
                                <div class="text-xs text-indigo-400">per {{ \Carbon\Carbon::parse($settlementDate)->format('d M Y') }}</div>
                            </div>
                            <div class="text-2xl font-bold text-indigo-800 font-mono">
                                Rp {{ number_format($calc['total_pelunasan'], 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Info note --}}
                    <p class="text-xs text-gray-400 mt-3">
                        * Perhitungan ini bersifat estimasi dan dapat berubah sesuai tanggal pelunasan aktual.
                        Outstanding = sisa total kewajiban (pokok + bunga + admin + asuransi).
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
