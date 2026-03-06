<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan — Rekap Kas</h2>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-700">Periode Laporan</span>
            </div>
            <div class="p-5">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div class="flex items-end gap-2 flex-wrap">
                        <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Tampilkan
                        </button>
                        <a href="{{ route('admin.reports.cash') }}"
                           class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                            Reset
                        </a>
                        <x-export-button route="admin.reports.cash.export" />
                        <a href="{{ route('admin.reports.cash.print', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary per Kas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Ringkasan per Akun Kas</span>
                    <span class="text-xs text-gray-400">{{ $dateFrom }} s/d {{ $dateTo }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">Net = Pemasukan &minus; Pengeluaran + Transfer Masuk &minus; Transfer Keluar</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Akun Kas</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pemasukan</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pengeluaran</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Transfer Masuk</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Transfer Keluar</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Net</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($accounts as $acc)
                            @php($s = $summary[$acc->id] ?? ['income'=>0,'expense'=>0,'transfer_in'=>0,'transfer_out'=>0,'net'=>0])
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $acc->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-right text-green-700">Rp {{ number_format($s['income'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-sm text-right text-red-600">Rp {{ number_format($s['expense'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-sm text-right text-gray-700">Rp {{ number_format($s['transfer_in'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-sm text-right text-gray-700">Rp {{ number_format($s['transfer_out'], 0, ',', '.') }}</td>
                                <td class="px-5 py-3.5 text-sm text-right font-bold {{ $s['net'] >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                    Rp {{ number_format($s['net'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Detail Transactions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <div class="text-sm font-medium text-gray-700">Detail Transaksi Kas</div>
                <p class="text-xs text-gray-400 mt-0.5">Hanya transaksi aktif (bukan VOID).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dari</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ke</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Uraian</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-500 font-mono">{{ $tx->code }}</td>
                                <td class="px-5 py-3.5">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset',
                                        'bg-green-50 text-green-700 ring-green-200' => $tx->type === 'INCOME',
                                        'bg-red-50 text-red-700 ring-red-200'       => $tx->type === 'EXPENSE',
                                        'bg-blue-50 text-blue-700 ring-blue-200'    => $tx->type === 'TRANSFER',
                                        'bg-gray-50 text-gray-600 ring-gray-200'    => !in_array($tx->type, ['INCOME','EXPENSE','TRANSFER']),
                                    ])>
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">
                                    @if($tx->type === 'EXPENSE' || $tx->type === 'TRANSFER')
                                        {{ $tx->fromCashAccount?->name }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">
                                    @if($tx->type === 'INCOME' || $tx->type === 'TRANSFER')
                                        {{ $tx->toCashAccount?->name }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $tx->category }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-600">{{ $tx->description }}</td>
                                <td class="px-5 py-3.5 text-sm text-right font-semibold text-gray-800 whitespace-nowrap">
                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $tx->creator?->name ?? $tx->creator?->email }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    Tidak ada transaksi pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
