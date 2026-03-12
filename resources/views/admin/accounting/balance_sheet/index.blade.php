<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Neraca (Balance Sheet)</h2>
            <div class="flex gap-2 print:hidden">
            <a href="{{ route('admin.accounting.balance_sheet.export', array_filter(['date_to'=>$dateTo])) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                ↓ Export Excel
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak PDF
            </button>
            </div>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } }</style>

    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Per Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.accounting.balance_sheet.index') }}"
                       class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
                <div class="flex items-center pb-0.5">
                    <span class="text-xs text-gray-400">Neraca menampilkan saldo kumulatif dari awal hingga tanggal yang dipilih.</span>
                </div>
            </form>
        </div>

        {{-- Balance status --}}
        @if($isBalanced)
            <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Neraca <strong>seimbang</strong> — Total Aktiva = Total Kewajiban + Modal.
            </div>
        @else
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                Neraca <strong>tidak seimbang</strong> — Selisih: Rp {{ number_format(abs($difference), 0, ',', '.') }}. Periksa jurnal yang belum terposting.
            </div>
        @endif

        {{-- Statement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- Statement header --}}
            <div class="px-6 py-5 border-b border-gray-100 text-center">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Koperasi Simpan Pinjam</div>
                <div class="text-lg font-bold text-gray-800">NERACA</div>
                <div class="text-sm text-gray-500 mt-1">
                    Per tanggal {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d F Y') }}
                </div>
            </div>

            <div class="divide-y divide-gray-100">

                {{-- AKTIVA --}}
                <div class="px-6 py-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Aktiva</div>

                    @forelse($assets as $r)
                        <div class="flex justify-between items-baseline py-1.5 {{ $loop->last ? '' : 'border-b border-gray-50' }}
                            {{ $r->amount < 0 ? 'text-amber-700' : '' }}">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-xs text-gray-400 w-12 shrink-0">{{ $r->code }}</span>
                                <span class="text-sm {{ $r->amount < 0 ? 'text-amber-700' : 'text-gray-700' }}">
                                    {{ $r->name }}
                                    @if($r->amount < 0)
                                        <span class="text-xs font-medium">(saldo tidak normal)</span>
                                    @endif
                                </span>
                            </div>
                            <span class="text-sm tabular-nums ml-4 shrink-0 {{ $r->amount < 0 ? 'text-amber-700 font-medium' : 'text-gray-800' }}">
                                Rp {{ number_format($r->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 italic">Tidak ada akun aktiva dengan saldo.</div>
                    @endforelse

                    <div class="flex justify-between items-baseline mt-4 pt-3 border-t-2 border-gray-300">
                        <span class="text-sm font-bold text-gray-800 uppercase tracking-wide">Total Aktiva</span>
                        <span class="text-sm font-bold text-gray-900 tabular-nums">
                            Rp {{ number_format($totalAssets, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- KEWAJIBAN --}}
                <div class="px-6 py-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Kewajiban</div>

                    @forelse($liabilities as $r)
                        <div class="flex justify-between items-baseline py-1.5 {{ $loop->last ? '' : 'border-b border-gray-50' }}">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-xs text-gray-400 w-12 shrink-0">{{ $r->code }}</span>
                                <span class="text-sm text-gray-700">{{ $r->name }}</span>
                            </div>
                            <span class="text-sm text-gray-800 tabular-nums ml-4 shrink-0">
                                Rp {{ number_format($r->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 italic">Tidak ada akun kewajiban dengan saldo.</div>
                    @endforelse

                    <div class="flex justify-between items-baseline mt-4 pt-3 border-t border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Total Kewajiban</span>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums">
                            Rp {{ number_format($totalLiabilities, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- MODAL --}}
                <div class="px-6 py-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Modal</div>

                    @forelse($equities as $r)
                        <div class="flex justify-between items-baseline py-1.5 {{ $loop->last ? '' : 'border-b border-gray-50' }}">
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono text-xs text-gray-400 w-12 shrink-0">{{ $r->code }}</span>
                                <span class="text-sm text-gray-700">{{ $r->name }}</span>
                            </div>
                            <span class="text-sm text-gray-800 tabular-nums ml-4 shrink-0">
                                Rp {{ number_format($r->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-sm text-gray-400 italic">Tidak ada akun modal.</div>
                    @endforelse

                    {{-- Cumulative net income — flows from P&L, replaces closing entries --}}
                    <div class="flex justify-between items-baseline py-1.5 border-t border-gray-100 mt-1">
                        <div class="flex items-baseline gap-3">
                            <span class="font-mono text-xs text-gray-400 w-12 shrink-0">—</span>
                            <span class="text-sm text-gray-700 italic">
                                {{ $cumulativeNetIncome >= 0 ? 'Akumulasi Laba' : 'Akumulasi Rugi' }}
                                <span class="text-xs text-gray-400 not-italic">(dari seluruh periode)</span>
                            </span>
                        </div>
                        <span class="text-sm tabular-nums ml-4 shrink-0 {{ $cumulativeNetIncome < 0 ? 'text-red-700 font-medium' : 'text-gray-800' }}">
                            Rp {{ number_format($cumulativeNetIncome, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-baseline mt-3 pt-3 border-t border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Total Modal</span>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums">
                            Rp {{ number_format($totalEquitySection, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- GRAND TOTAL --}}
                <div class="px-6 py-5 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-800 uppercase tracking-wide">Total Kewajiban + Modal</span>
                        <span class="text-sm font-bold text-gray-900 tabular-nums">
                            Rp {{ number_format($totalLiabilityEquity, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Balance check --}}
                    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center text-xs">
                        <span class="text-gray-500">Total Aktiva</span>
                        <span class="tabular-nums text-gray-700">Rp {{ number_format($totalAssets, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs mt-1">
                        <span class="text-gray-500">Total Kewajiban + Modal</span>
                        <span class="tabular-nums text-gray-700">Rp {{ number_format($totalLiabilityEquity, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs mt-1 pt-1.5 border-t border-gray-200 font-semibold">
                        <span class="{{ $isBalanced ? 'text-green-600' : 'text-red-600' }}">
                            Selisih {{ $isBalanced ? '(Seimbang ✓)' : '(Tidak Seimbang ✗)' }}
                        </span>
                        <span class="tabular-nums {{ $isBalanced ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($difference, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-admin-layout>
