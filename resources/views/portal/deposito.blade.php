@extends('layouts.portal')

@section('content')
<div class="space-y-4">

    <h2 class="text-lg font-bold text-gray-800">Deposito Saya</h2>

    @if($depositos->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada kontrak deposito.</p>
        </div>
    @else
        @foreach($depositos as $d)
            @php
                $disp = $d->displayStatus();
                $statusClass = match(true) {
                    $disp === 'JATUH TEMPO' => 'bg-amber-100 text-amber-800',
                    $disp === 'ACTIVE'      => 'bg-emerald-100 text-emerald-800',
                    $disp === 'WITHDRAWN'   => 'bg-gray-200 text-gray-700',
                    default                 => 'bg-gray-100 text-gray-600',
                };
                $daysLeft = $d->daysToMaturity();
            @endphp
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-700">{{ $d->savingProduct->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $d->reference_code }}</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-bold rounded {{ $statusClass }}">{{ $disp }}</span>
                </div>

                <div class="px-5 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Pokok</div>
                        <div class="font-bold text-gray-900 tabular-nums">Rp {{ number_format($d->principal_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Bunga / Tenor</div>
                        <div class="font-semibold text-gray-800">
                            {{ rtrim(rtrim(number_format((float)$d->interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / {{ $d->term_months }} bln
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Bunga (proyeksi)</div>
                        <div class="font-bold text-emerald-700 tabular-nums">Rp {{ number_format($d->interestAmount(), 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wide">Total Payout</div>
                        <div class="font-bold text-indigo-700 tabular-nums">Rp {{ number_format($d->payoutAmount(), 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-600">
                    <span>Mulai: <strong>{{ $d->start_date->format('d/m/Y') }}</strong></span>
                    <span>Jatuh tempo: <strong>{{ $d->maturity_date->format('d/m/Y') }}</strong></span>
                    <span>
                        @if($d->status === 'ACTIVE')
                            @if($daysLeft > 0)
                                {{ $daysLeft }} hari lagi
                            @elseif($daysLeft === 0)
                                Jatuh tempo hari ini
                            @else
                                Sudah jatuh tempo {{ abs($daysLeft) }} hari yang lalu
                            @endif
                        @elseif($d->status === 'WITHDRAWN')
                            Sudah dicairkan
                        @else
                            {{ $d->status }}
                        @endif
                    </span>
                </div>
            </div>
        @endforeach
    @endif

</div>
@endsection
