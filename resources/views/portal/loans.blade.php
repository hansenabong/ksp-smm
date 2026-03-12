@extends('layouts.portal')

@section('content')
<div class="space-y-4">

    <h2 class="text-lg font-bold text-gray-800">Riwayat Pinjaman</h2>

    @if($loans->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada data pinjaman.</p>
        </div>
    @else
        @foreach($loans as $loan)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Loan header --}}
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $loan->loanProduct->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Plafon: <strong>Rp {{ number_format($loan->principal, 0, ',', '.') }}</strong>
                            · Tenor: <strong>{{ $loan->tenor_months }} bln</strong>
                            · Cair: <strong>{{ $loan->disbursed_date?->format('d/m/Y') }}</strong>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @if($loan->status === 'ACTIVE')
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Lunas</span>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">Sisa: <span class="font-bold text-red-600">Rp {{ number_format($loan->outstanding, 0, ',', '.') }}</span></p>
                    </div>
                </div>

                {{-- progress bar --}}
                @php
                    $pct = $loan->total_payable > 0
                        ? min(100, round(($loan->total_paid / $loan->total_payable) * 100))
                        : 0;
                @endphp
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Dibayar: Rp {{ number_format($loan->total_paid, 0, ',', '.') }}</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Payment history --}}
            @if($loan->payments->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-2 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-2 text-right font-semibold">Jumlah</th>
                            <th class="px-4 py-2 text-right font-semibold">Denda</th>
                            <th class="px-4 py-2 text-left font-semibold">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($loan->payments->take(10) as $pay)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ \Carbon\Carbon::parse($pay->trx_date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right font-semibold text-gray-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right text-xs {{ ($pay->penalty_amount + $pay->early_repayment_penalty) > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                @if(($pay->penalty_amount + $pay->early_repayment_penalty) > 0)
                                    Rp {{ number_format($pay->penalty_amount + $pay->early_repayment_penalty, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $pay->note ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($loan->payments->count() > 10)
                    <p class="px-4 py-2 text-xs text-gray-400 border-t border-gray-100">
                        Menampilkan 10 dari {{ $loan->payments->count() }} pembayaran.
                    </p>
                @endif
            </div>
            @else
                <p class="px-5 py-3 text-xs text-gray-400">Belum ada riwayat pembayaran.</p>
            @endif
        </div>
        @endforeach
    @endif

</div>
@endsection
