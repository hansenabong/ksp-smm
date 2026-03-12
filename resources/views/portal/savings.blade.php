@extends('layouts.portal')

@section('content')
<div class="space-y-4">

    <h2 class="text-lg font-bold text-gray-800">Riwayat Simpanan</h2>

    @if($byProduct->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada data simpanan.</p>
        </div>
    @else
        @foreach($byProduct as $row)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">{{ $row['product']->name }}</p>
                <span class="text-sm font-bold text-indigo-700">Saldo: Rp {{ number_format($row['balance'], 0, ',', '.') }}</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-2 text-left font-semibold">Tanggal</th>
                        <th class="px-4 py-2 text-left font-semibold">Jenis</th>
                        <th class="px-4 py-2 text-right font-semibold">Jumlah</th>
                        <th class="px-4 py-2 text-left font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($row['transactions'] as $tx)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ $tx->type === 'DEPOSIT' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $tx->type === 'DEPOSIT' ? 'Setoran' : 'Penarikan' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right font-semibold
                            {{ $tx->type === 'DEPOSIT' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $tx->type === 'DEPOSIT' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $tx->description ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    @endif

</div>
@endsection
