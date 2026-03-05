<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Laba Rugi (Income Statement)</h2>
    </x-slot>

    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div class="flex items-center gap-2 pb-0.5">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="show_zero" value="1" @checked($showZero)
                               class="rounded border-gray-300">
                        Tampilkan akun saldo nol
                    </label>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.accounting.income_statement.index') }}"
                       class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Statement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- Statement header --}}
            <div class="px-6 py-5 border-b border-gray-100 text-center">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Koperasi Simpan Pinjam</div>
                <div class="text-lg font-bold text-gray-800">LAPORAN LABA RUGI</div>
                <div class="text-sm text-gray-500 mt-1">
                    Periode {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d F Y') }}
                    s.d. {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d F Y') }}
                </div>
            </div>

            <div class="px-6 py-5 space-y-6">

                {{-- PENDAPATAN --}}
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">
                        Pendapatan
                    </div>
                    @forelse($revenues as $r)
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
                        <div class="text-sm text-gray-400 italic py-2">Tidak ada pendapatan pada periode ini.</div>
                    @endforelse

                    <div class="flex justify-between items-baseline mt-3 pt-3 border-t border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Total Pendapatan</span>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- BEBAN --}}
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">
                        Beban
                    </div>
                    @forelse($expenses as $r)
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
                        <div class="text-sm text-gray-400 italic py-2">Tidak ada beban pada periode ini.</div>
                    @endforelse

                    <div class="flex justify-between items-baseline mt-3 pt-3 border-t border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Total Beban</span>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums">
                            Rp {{ number_format($totalExpense, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- NET INCOME --}}
                <div class="rounded-xl border-2 {{ $isProfit ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} px-5 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest {{ $isProfit ? 'text-green-600' : 'text-red-600' }} mb-0.5">
                                {{ $isProfit ? 'Laba Bersih' : 'Rugi Bersih' }}
                            </div>
                            <div class="text-xs text-gray-500">Total Pendapatan − Total Beban</div>
                        </div>
                        <div class="text-xl font-bold tabular-nums {{ $isProfit ? 'text-green-700' : 'text-red-700' }}">
                            @if(!$isProfit)(–) @endif
                            Rp {{ number_format(abs($netIncome), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-admin-layout>
