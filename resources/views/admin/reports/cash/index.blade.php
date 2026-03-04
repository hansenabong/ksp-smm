<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan - Rekap Kas</h2>
            <a href="{{ route('admin.home') }}" class="px-4 py-2 bg-gray-200 rounded">Kembali</a>
        </div>
    </x-slot>

    <div class="space-y-4">

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-sm">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="text-sm">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Tampilkan</button>
                        <a class="px-4 py-2 bg-gray-200 rounded" href="{{ route('admin.reports.cash') }}">Reset</a>
                        <x-export-button route="admin.reports.cash.export" />
                    </div>
                    
                </form>
            </div>

            {{-- Summary --}}
            <div class="bg-white rounded shadow overflow-x-auto">
                <div class="p-4 border-b">
                    <div class="font-semibold">Ringkasan per Kas ({{ $dateFrom }} s/d {{ $dateTo }})</div>
                    <div class="text-xs text-gray-500">Net = Income - Expense + Transfer In - Transfer Out</div>
                </div>

                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Kas</th>
                            <th class="text-right p-3">Income</th>
                            <th class="text-right p-3">Expense</th>
                            <th class="text-right p-3">Transfer In</th>
                            <th class="text-right p-3">Transfer Out</th>
                            <th class="text-right p-3">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $acc)
                            @php($s = $summary[$acc->id] ?? ['income'=>0,'expense'=>0,'transfer_in'=>0,'transfer_out'=>0,'net'=>0])
                            <tr class="border-t">
                                <td class="p-3 font-medium">{{ $acc->name }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($s['income'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($s['expense'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($s['transfer_in'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($s['transfer_out'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-semibold">
                                    Rp {{ number_format($s['net'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Detail --}}
            <div class="bg-white rounded shadow overflow-x-auto">
                <div class="p-4 border-b">
                    <div class="font-semibold">Detail Transaksi Kas</div>
                    <div class="text-xs text-gray-500">Hanya transaksi aktif (bukan VOID).</div>
                </div>

                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Tanggal</th>
                            <th class="text-left p-3">Kode</th>
                            <th class="text-left p-3">Type</th>
                            <th class="text-left p-3">Dari</th>
                            <th class="text-left p-3">Ke</th>
                            <th class="text-left p-3">Kategori</th>
                            <th class="text-left p-3">Uraian</th>
                            <th class="text-right p-3">Jumlah</th>
                            <th class="text-left p-3">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-t">
                                <td class="p-3">{{ $tx->trx_date }}</td>
                                <td class="p-3">{{ $tx->code }}</td>
                                <td class="p-3">{{ $tx->type }}</td>

                                <td class="p-3">
                                    @if($tx->type === 'EXPENSE' || $tx->type === 'TRANSFER')
                                        {{ $tx->fromCashAccount?->name }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="p-3">
                                    @if($tx->type === 'INCOME' || $tx->type === 'TRANSFER')
                                        {{ $tx->toCashAccount?->name }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="p-3">{{ $tx->category }}</td>
                                <td class="p-3">{{ $tx->description }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                <td class="p-3">{{ $tx->creator?->email }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-3" colspan="9">Tidak ada transaksi pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

    </div>
</x-admin-layout>