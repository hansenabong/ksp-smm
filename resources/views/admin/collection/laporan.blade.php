<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Laporan Collection</h2>
            <button onclick="window.print()" class="px-3 py-1 text-xs bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-600 print:hidden">
                Cetak
            </button>
        </div>
    </x-slot>

    <style>
        @media print { .print\:hidden { display:none!important; } }
    </style>

    @php
        $levelLabels = \App\Models\CollectionRecord::LEVELS;
        $bucketColors = [
            'lancar'   => ['bg' => 'bg-green-50',  'text' => 'text-green-700',  'border' => 'border-green-200',  'badge' => 'bg-green-100 text-green-700'],
            'b1_30'    => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-700'],
            'b31_60'   => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-200', 'badge' => 'bg-orange-100 text-orange-700'],
            'b61_plus' => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'border' => 'border-red-200',    'badge' => 'bg-red-100 text-red-700'],
        ];
    @endphp

    {{-- As-of date --}}
    <div class="text-xs text-gray-400 mb-3 print:block">Per tanggal: <strong>{{ $today->format('d F Y') }}</strong></div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
        @php
            $cards = [
                ['label'=>'Total Nasabah',     'value'=>number_format($summary['total_nasabah']),                          'color'=>'text-gray-700'],
                ['label'=>'Total Outstanding', 'value'=>'Rp '.number_format($summary['total_outstanding']),                'color'=>'text-indigo-700'],
                ['label'=>'Nasabah NPL',       'value'=>number_format($summary['npl_nasabah']),                            'color'=>'text-red-700'],
                ['label'=>'Outstanding NPL',   'value'=>'Rp '.number_format($summary['npl_outstanding']),                  'color'=>'text-red-700'],
                ['label'=>'NPL Ratio',         'value'=>$summary['npl_pct'].'%',                                           'color'=>$summary['npl_pct']>5?'text-red-600 font-bold':'text-green-700'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">{{ $card['label'] }}</div>
            <div class="text-sm font-semibold {{ $card['color'] }} mt-0.5">{{ $card['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Collectability Table ── --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Kolektabilitas Pinjaman</div>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Uraian</th>
                    <th class="px-4 py-3 text-right">Nasabah</th>
                    <th class="px-4 py-3 text-right">Presentase</th>
                    <th class="px-4 py-3 text-right">Jumlah Outstanding (Rp)</th>
                    <th class="px-4 py-3 text-right">Tunggakan (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $totalCount = $summary['total_nasabah']; @endphp
                @foreach($buckets as $key => $bucket)
                @php $c = $bucketColors[$key]; @endphp
                <tr class="{{ $c['bg'] }}">
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2.5">
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $c['badge'] }}">
                            {{ $bucket['label'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right font-semibold {{ $c['text'] }}">
                        {{ number_format($bucket['count']) }}
                    </td>
                    <td class="px-4 py-2.5 text-right {{ $c['text'] }}">
                        {{ $totalCount > 0 ? number_format($bucket['count'] / $totalCount * 100, 1) : 0 }}%
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono {{ $c['text'] }}">
                        {{ number_format($bucket['outstanding']) }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono {{ $bucket['arrears'] > 0 ? 'text-red-600 font-semibold' : $c['text'] }}">
                        {{ $bucket['arrears'] > 0 ? number_format($bucket['arrears']) : '-' }}
                    </td>
                </tr>
                @endforeach

                {{-- NPL subtotal (b1_30 + b31_60 + b61_plus) --}}
                @php
                    $nplCount   = $buckets['b1_30']['count'] + $buckets['b31_60']['count'] + $buckets['b61_plus']['count'];
                    $nplOutst   = $buckets['b1_30']['outstanding'] + $buckets['b31_60']['outstanding'] + $buckets['b61_plus']['outstanding'];
                    $nplArrears = $buckets['b1_30']['arrears'] + $buckets['b31_60']['arrears'] + $buckets['b61_plus']['arrears'];
                @endphp
                <tr class="bg-red-100 font-bold">
                    <td class="px-4 py-2.5 text-red-700"></td>
                    <td class="px-4 py-2.5 text-red-700">Jumlah NPL</td>
                    <td class="px-4 py-2.5 text-right text-red-700">{{ number_format($nplCount) }}</td>
                    <td class="px-4 py-2.5 text-right text-red-700">
                        {{ $totalCount > 0 ? number_format($nplCount / $totalCount * 100, 1) : 0 }}%
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-red-700">{{ number_format($nplOutst) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-red-700">{{ number_format($nplArrears) }}</td>
                </tr>

                {{-- Grand total --}}
                <tr class="bg-amber-400 font-bold">
                    <td class="px-4 py-2.5"></td>
                    <td class="px-4 py-2.5">TOTAL</td>
                    <td class="px-4 py-2.5 text-right">{{ number_format($totalCount) }}</td>
                    <td class="px-4 py-2.5 text-right">100%</td>
                    <td class="px-4 py-2.5 text-right font-mono">{{ number_format($summary['total_outstanding']) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono">
                        {{ number_format($nplArrears) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── Surat Peringatan Counts ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Surat Peringatan — Rekapitulasi</div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Jenis Surat</th>
                        <th class="px-4 py-2 text-right">Jumlah Diterbitkan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach(\App\Models\CollectionLetter::TYPES as $type => $label)
                    <tr>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                {{ match($type) {
                                    'SP1'    => 'bg-yellow-100 text-yellow-700',
                                    'SP2'    => 'bg-orange-100 text-orange-700',
                                    'SP3'    => 'bg-red-100 text-red-700',
                                    'SOMASI' => 'bg-purple-100 text-purple-700',
                                    'CESSIE' => 'bg-rose-100 text-rose-700',
                                    default  => 'bg-gray-100 text-gray-600',
                                } }}">{{ $label }}</span>
                        </td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-700">
                            {{ number_format($spCounts[$type] ?? 0) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-4 py-2 text-gray-700">Total Surat</td>
                        <td class="px-4 py-2 text-right text-gray-700">{{ number_format($spCounts->sum()) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Escalation Level Distribution --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Distribusi Eskalasi Penagihan</div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Level</th>
                        <th class="px-4 py-2 text-right">Nasabah</th>
                        <th class="px-4 py-2 text-right">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach(\App\Models\CollectionRecord::LEVELS as $lvl => $lbl)
                    @php
                        $cnt = $loans->filter(fn($l) => ($l->collectionRecord?->escalation_level ?? 0) == $lvl)->count();
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-gray-700">{{ $lbl }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-gray-800">{{ $cnt }}</td>
                        <td class="px-4 py-2 text-right text-gray-500">
                            {{ $totalCount > 0 ? number_format($cnt / $totalCount * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
