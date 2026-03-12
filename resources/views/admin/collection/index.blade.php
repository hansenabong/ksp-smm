<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Master Tagihan Nasabah</h2>
            <div class="flex gap-2 print:hidden">
                <a href="{{ route('admin.collection.export', array_filter(['q'=>$search,'level'=>$levelFilter,'petugas_id'=>$petugasFilter,'sort_by'=>$sortBy])) }}"
                   class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    ↓ Export Excel
                </a>
                <button onclick="window.print()" class="px-3 py-1 text-xs bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-600">
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .print\:hidden { display: none !important; }
            body { font-size: 7pt; }
            .master-table th, .master-table td { padding: 2pt 3pt !important; font-size: 7pt; }
        }
        .master-table th { background-color: #F59E0B; color: #000; font-weight: bold; font-size: 10px; text-align: center; padding: 5px 6px; border: 1px solid #D97706; white-space: nowrap; }
        .master-table td { font-size: 11px; padding: 4px 6px; border: 1px solid #E5E7EB; vertical-align: middle; white-space: nowrap; }
        .master-table tr:nth-child(even) td { background-color: #FFFBEB; }
        .master-table tr:hover td { background-color: #FEF3C7; }
        .master-table .total-row td { background-color: #F59E0B !important; font-weight: bold; border: 1px solid #D97706; }
        .col-no       { width: 30px; text-align: center; }
        .col-name     { min-width: 160px; }
        .col-kontrak  { min-width: 130px; }
        .col-cabang   { min-width: 80px; text-align: center; }
        .col-kota     { min-width: 90px; text-align: center; }
        .col-num      { min-width: 90px; text-align: right; font-family: monospace; }
        .col-sm       { min-width: 40px; text-align: center; }
        .col-neg      { color: #dc2626; }
        .col-addr     { min-width: 220px; }
        .col-phone    { min-width: 130px; text-align: center; }
    </style>

    @php
        $levelLabels = \App\Models\CollectionRecord::LEVELS;
        $levelColors = \App\Models\CollectionRecord::LEVEL_COLORS;
        $badgeClass  = function(int $level) use ($levelColors) {
            return match($levelColors[$level] ?? 'gray') {
                'yellow' => 'bg-yellow-100 text-yellow-700',
                'orange' => 'bg-orange-100 text-orange-700',
                'red'    => 'bg-red-100 text-red-700',
                'purple' => 'bg-purple-100 text-purple-700',
                'rose'   => 'bg-rose-100 text-rose-700',
                default  => 'bg-gray-100 text-gray-600',
            };
        };
    @endphp

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-3 print:hidden">
        <form method="GET" action="{{ route('admin.collection.index') }}" class="flex flex-wrap gap-3 px-4 py-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cari Nasabah</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Nama / No. Anggota"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 w-44">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Eskalasi</label>
                <select name="level" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <option value="">Semua</option>
                    @foreach($levelLabels as $v => $l)
                    <option value="{{ $v }}" @selected($levelFilter == $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Petugas</label>
                <select name="petugas_id" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <option value="">Semua</option>
                    @foreach($petugasList as $pt)
                    <option value="{{ $pt->id }}" @selected($petugasFilter == $pt->id)>{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Urutkan</label>
                <select name="sort_by" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <option value="">Default (ID)</option>
                    <option value="alamat_ktp"     @selected($sortBy === 'alamat_ktp')>Alamat KTP</option>
                    <option value="alamat_jaminan" @selected($sortBy === 'alamat_jaminan')>Alamat Jaminan</option>
                    <option value="alamat_tinggal" @selected($sortBy === 'alamat_tinggal')>Alamat Tinggal</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-amber-500 text-white text-sm rounded-lg hover:bg-amber-600 font-medium">Filter</button>
            @if($search || $levelFilter !== null || $petugasFilter || $sortBy)
            <a href="{{ route('admin.collection.index') }}" class="px-4 py-1.5 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">Reset</a>
            @endif
        </form>
    </div>

    {{-- ====== MAIN TABLE ====== --}}
    <div class="bg-white border border-amber-300 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="master-table border-collapse w-full">
                {{-- ── Header row 1 (group labels) ── --}}
                <thead>
                <tr>
                    <th rowspan="3" class="col-no">No</th>
                    <th rowspan="3" class="col-name">Nasabah</th>
                    <th rowspan="3" class="col-kontrak">Nomor Kontrak</th>
                    <th rowspan="3" class="col-cabang">Cabang</th>
                    <th rowspan="3" class="col-kota">Kota<br>(Tempat Tinggal)</th>
                    <th rowspan="3" class="col-kota">Kota<br>(Jaminan)</th>
                    <th rowspan="3" class="col-num">Pinjaman</th>
                    {{-- JW group --}}
                    <th colspan="4">JW</th>
                    {{-- Angsuran group --}}
                    <th colspan="2">Angsuran</th>
                    <th rowspan="3" class="col-num">Total<br>Kewajiban</th>
                    {{-- Realisasi --}}
                    <th colspan="3">Realisasi</th>
                    <th rowspan="3" class="col-num">Sisa<br>Kewajiban</th>
                    <th rowspan="3" class="col-num">TOTAL<br>BAYAR</th>
                    <th rowspan="3" class="col-addr">Alamat Tinggal</th>
                    <th rowspan="3" class="col-addr">Alamat Jaminan</th>
                    <th rowspan="3" class="col-phone">Contact Person</th>
                    <th rowspan="3" class="print:hidden" style="min-width:60px">Status</th>
                    <th rowspan="3" class="print:hidden" style="min-width:50px">Aksi</th>
                </tr>
                <tr>
                    {{-- JW sub --}}
                    <th rowspan="2" class="col-sm">Tnr</th>
                    <th rowspan="2" class="col-sm">Ke</th>
                    {{-- Bejalan --}}
                    <th colspan="2">Bejalan</th>
                    {{-- Angsuran sub --}}
                    <th rowspan="2" class="col-sm">Bln</th>
                    <th rowspan="2" class="col-num">Tunggakan<br>(Rp)</th>
                    {{-- Realisasi sub --}}
                    <th rowspan="2" class="col-sm">Tgl</th>
                    <th rowspan="2" class="col-num">Rp.</th>
                    <th rowspan="2" class="col-num">Tunggakan</th>
                </tr>
                <tr>
                    <th class="col-sm">Tgl</th>
                    <th class="col-num">Rp.</th>
                </tr>
                </thead>

                {{-- ── Data rows ── --}}
                <tbody>
                @forelse($loans as $i => $loan)
                @php
                    $rec   = $loan->collectionRecord;
                    $level = $rec?->escalation_level ?? 0;
                    $noPk  = $loan->application?->notaris_deed_number
                          ?: ('PK-KSP/'.str_pad($loan->id, 5, '0', STR_PAD_LEFT).'/'.date('Y', strtotime($loan->disbursed_date)));
                    $rowNo = method_exists($loans, 'firstItem') ? $loans->firstItem() + $i : $loop->iteration;
                @endphp
                <tr>
                    <td class="col-no text-gray-500">{{ $rowNo }}</td>
                    <td class="col-name">
                        <strong>{{ strtoupper($loan->member->name) }}</strong>
                        @if($rec?->is_restruk)<br><span style="font-size:9px;color:#2563EB">[Restruk]</span>@endif
                    </td>
                    <td class="col-kontrak text-gray-600">{{ $noPk }}</td>
                    <td class="col-cabang">{{ $rec?->branch ?? '-' }}</td>
                    {{-- Kota Tempat Tinggal (last word of address as rough city) --}}
                    <td class="col-kota">{{ $loan->member->address ? last(explode(' ', trim($loan->member->address))) : '-' }}</td>
                    <td class="col-kota">{{ $loan->_kota_jaminan ?: '-' }}</td>
                    <td class="col-num">{{ number_format($loan->principal) }}</td>
                    {{-- JW --}}
                    <td class="col-sm">{{ $loan->tenor_months }}</td>
                    <td class="col-sm">{{ $loan->_ke }}</td>
                    <td class="col-sm" style="font-size:9px">{{ $loan->_bejalan_tgl }}</td>
                    <td class="col-num">{{ number_format($loan->_angsuran_berjalan) }}</td>
                    {{-- Angsuran --}}
                    <td class="col-sm {{ $loan->_bln_telat > 0 ? 'col-neg' : '' }}">
                        {{ $loan->_bln_telat != 0 ? $loan->_bln_telat : '' }}
                    </td>
                    <td class="col-num {{ $loan->_tunggakan < 0 ? 'col-neg' : '' }}">
                        {{ $loan->_tunggakan != 0 ? number_format($loan->_tunggakan) : '' }}
                    </td>
                    {{-- Total Kewajiban --}}
                    <td class="col-num {{ $loan->_total_kewajiban < 0 ? 'col-neg' : '' }}">
                        {{ number_format($loan->_total_kewajiban) }}
                    </td>
                    {{-- Realisasi --}}
                    <td class="col-sm" style="font-size:9px">{{ $loan->_realisasi_tgl ?? '' }}</td>
                    <td class="col-num">{{ $loan->_realisasi_rp ? number_format($loan->_realisasi_rp) : '' }}</td>
                    <td class="col-num {{ $loan->_realisasi_tunggakan < 0 ? 'col-neg' : '' }}">
                        {{ $loan->_realisasi_tunggakan != 0 ? number_format($loan->_realisasi_tunggakan) : '' }}
                    </td>
                    {{-- Sisa / Total --}}
                    <td class="col-num">{{ number_format($loan->_sisa_kewajiban) }}</td>
                    <td class="col-num">{{ number_format($loan->_total_bayar) }}</td>
                    {{-- Addresses --}}
                    <td class="col-addr" style="white-space:normal;font-size:9px">{{ $loan->_alamat_tinggal }}</td>
                    <td class="col-addr" style="white-space:normal;font-size:9px">{{ $loan->_alamat_jaminan }}</td>
                    {{-- Contact --}}
                    <td class="col-phone">{{ $loan->member->phone ?? '-' }}</td>
                    {{-- Status badge --}}
                    <td class="print:hidden text-center">
                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-semibold {{ $badgeClass($level) }}">
                            {{ $levelLabels[$level] }}
                        </span>
                    </td>
                    {{-- Action --}}
                    <td class="print:hidden text-center">
                        <a href="{{ route('admin.collection.show', $loan) }}"
                           class="text-xs text-indigo-600 hover:underline font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="25" class="text-center py-8 text-gray-400">Tidak ada data pinjaman aktif.</td>
                </tr>
                @endforelse

                {{-- ── Totals row ── --}}
                @if($loans->count() > 0)
                <tr class="total-row">
                    <td colspan="7" class="text-right">TOTAL</td>
                    <td colspan="4"></td>
                    <td></td>
                    <td class="col-num col-neg">{{ number_format($loans->getCollection()->sum('_tunggakan')) }}</td>
                    <td class="col-num">{{ number_format($loans->getCollection()->sum('_total_kewajiban')) }}</td>
                    <td></td>
                    <td class="col-num">{{ number_format($loans->getCollection()->sum('_realisasi_rp')) }}</td>
                    <td></td>
                    <td class="col-num">{{ number_format($loans->getCollection()->sum('_sisa_kewajiban')) }}</td>
                    <td class="col-num">{{ number_format($loans->getCollection()->sum('_total_bayar')) }}</td>
                    <td colspan="5"></td>
                </tr>
                @endif
                </tbody>
            </table>
        </div>

        @if(method_exists($loans, 'hasPages') && $loans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 print:hidden">
            {{ $loans->links() }}
        </div>
        @endif
    </div>

    {{-- ====== SUMMARY TABLE (bottom right, like Excel) ====== --}}
    <div class="mt-4 flex justify-end print:justify-start">
        <div class="bg-white border border-amber-300 rounded-xl shadow-sm overflow-hidden" style="min-width:420px">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase bg-amber-400" style="width:50%"></th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase bg-amber-400 border-l border-amber-300">Target</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase bg-amber-400 border-l border-amber-300">Realisasi</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase bg-amber-400 border-l border-amber-300">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                    @php
                        $ttRp  = $totals['total_tagihan'];
                        $tgRp  = $totals['target'];       // 65%
                        $abRp  = $totals['angsuran_berjalan'];
                        $tnRp  = -$totals['tunggakan'];   // negative
                        $rlRp  = $totals['total_realisasi'];
                        $pctFmt = fn($r, $t) => $t != 0 ? number_format(abs($r/$t)*100, 0).'%' : '0%';
                    @endphp
                    <tr>
                        <td class="px-4 py-1.5 text-gray-700 font-medium">Total Tagihan</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-800">{{ number_format($ttRp) }}</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-400"></td>
                        <td class="px-4 py-1.5 text-right text-gray-400"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-1.5 text-gray-700 font-medium">Target (65%)</td>
                        <td class="px-4 py-1.5 text-right font-mono text-red-600 font-semibold">{{ number_format($tgRp) }}</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-400"></td>
                        <td class="px-4 py-1.5 text-right font-bold text-amber-600">65%</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-1.5 text-gray-700 font-medium">Angsuran Berjalan</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-800">{{ number_format($abRp) }}</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-800">{{ number_format($rlRp) }}</td>
                        <td class="px-4 py-1.5 text-right {{ $abRp > 0 ? 'text-gray-700' : 'text-gray-400' }}">{{ $pctFmt($rlRp, $abRp) }}</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-1.5 text-gray-700 font-medium">Tunggakan</td>
                        <td class="px-4 py-1.5 text-right font-mono text-red-600">{{ number_format(-$tnRp) }}</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-400">0</td>
                        <td class="px-4 py-1.5 text-right text-gray-400">0%</td>
                    </tr>
                    <tr class="bg-amber-50">
                        <td class="px-4 py-1.5 font-bold text-gray-800">Total Realisasi Tagihan</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-400"></td>
                        <td class="px-4 py-1.5 text-right font-mono font-bold text-red-600">{{ number_format($rlRp) }}</td>
                        <td class="px-4 py-1.5 text-right font-bold {{ $rlRp > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $pctFmt($rlRp, $tgRp) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-1.5 text-gray-700 font-medium">Total Uang Masuk</td>
                        <td class="px-4 py-1.5 text-right font-mono text-gray-400"></td>
                        <td class="px-4 py-1.5 text-right font-mono font-bold text-green-700">{{ number_format($rlRp) }}</td>
                        <td class="px-4 py-1.5 text-right text-gray-400"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
