<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Pinjaman</h2>
            <x-export-button route="admin.reports.loans.export" />
        </div>
    </x-slot>

    <div class="space-y-4">

        @if(session('success'))
            <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                    <select name="status"
                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="ACTIVE" @selected($status === 'ACTIVE')>Aktif</option>
                        <option value="CLOSED" @selected($status === 'CLOSED')>Lunas</option>
                        <option value="ALL"    @selected($status === 'ALL')>Semua</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Tampilkan
                    </button>
                    <a href="{{ route('admin.reports.loans') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pinjaman</div>
                <div class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totals->total_count) }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    @if($status === 'ALL') semua status @elseif($status === 'CLOSED') lunas @else aktif @endif
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pokok</div>
                <div class="mt-1 text-lg font-bold text-gray-800">Rp {{ number_format($totals->total_principal, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-0.5">tersalur</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sudah Dibayar</div>
                <div class="mt-1 text-lg font-bold text-green-600">Rp {{ number_format($totals->total_paid, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-0.5">total penerimaan</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sisa Outstanding</div>
                <div class="mt-1 text-lg font-bold {{ $status === 'CLOSED' ? 'text-gray-500' : 'text-red-600' }}">
                    Rp {{ number_format($totals->total_outstanding, 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-400 mt-0.5">belum terbayar</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Rekap Pinjaman</span>
                <span class="text-xs text-gray-400">{{ $loans->total() }} data</span>
            </div>

            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="text-left px-4 py-3">Pinjaman</th>
                        <th class="text-left px-4 py-3">Anggota</th>
                        <th class="text-right px-4 py-3">Pokok & Angsuran</th>
                        <th class="text-left px-4 py-3 w-40">Progres</th>
                        <th class="text-right px-4 py-3">Sisa</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($loans as $loan)
                        @php
                            $pct = $loan->total_payable > 0
                                ? min(100, round(($loan->total_paid / $loan->total_payable) * 100))
                                : 0;
                            [$bg, $dot] = match($loan->status) {
                                'ACTIVE' => ['bg-green-50 text-green-700 ring-green-200', 'bg-green-400'],
                                'CLOSED' => ['bg-gray-50 text-gray-600 ring-gray-200',   'bg-gray-400'],
                                default  => ['bg-gray-50 text-gray-600 ring-gray-200',   'bg-gray-400'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Pinjaman: ID + tanggal + produk --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold text-gray-800">#{{ $loan->id }}</div>
                                <div class="text-xs text-gray-400">{{ $loan->disbursed_date?->format('d/m/Y') ?? '-' }}</div>
                                <div class="text-xs text-indigo-500 mt-0.5">{{ $loan->loanProduct?->name ?? '-' }}</div>
                            </td>

                            {{-- Anggota --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $loan->member?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $loan->member?->member_no ?? '-' }}</div>
                            </td>

                            {{-- Pokok + tenor + angsuran --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-semibold text-gray-800">Rp {{ number_format($loan->principal ?? 0, 0, ',', '.') }}</div>
                                <div class="text-xs text-gray-400">{{ $loan->tenor_months }} bln</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    Rp {{ number_format($loan->monthly_installment ?? 0, 0, ',', '.') }}/bln
                                </div>
                            </td>

                            {{-- Progress --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                        <div class="{{ $loan->status === 'CLOSED' ? 'bg-gray-400' : 'bg-green-500' }} h-1.5 rounded-full transition-all"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 tabular-nums w-8 text-right shrink-0">{{ $pct }}%</span>
                                </div>
                                <div class="text-xs text-gray-400 tabular-nums mt-1">
                                    {{ number_format($loan->total_paid ?? 0, 0, ',', '.') }}
                                    <span class="text-gray-300">/</span>
                                    {{ number_format($loan->total_payable ?? 0, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Sisa --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="font-bold {{ $loan->outstanding > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    Rp {{ number_format($loan->outstanding ?? 0, 0, ',', '.') }}
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset {{ $bg }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                                    {{ $loan->status === 'ACTIVE' ? 'Aktif' : 'Lunas' }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.loans.active.show', $loan) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                    Detail &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                                Belum ada data pinjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            </div>{{-- overflow-x-auto --}}

            @if($loans->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
