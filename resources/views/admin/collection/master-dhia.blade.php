<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Master Data (Admin Data)</h2>
            <div class="flex gap-2 print:hidden">
                <a href="{{ route('admin.collection.export-dhia', array_filter(['q'=>$search,'wilayah'=>$wilayah,'wilayah_from'=>$wilayahFrom,'sort_by'=>$sortBy])) }}"
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
            .dhia-table th, .dhia-table td { padding: 2pt 3pt !important; font-size: 7pt; }
        }
        .dhia-table th {
            background-color: #F59E0B; color: #000; font-weight: bold; font-size: 9px;
            text-align: center; padding: 4px 5px; border: 1px solid #D97706; white-space: nowrap;
        }
        .dhia-table td {
            font-size: 10px; padding: 3px 5px; border: 1px solid #E5E7EB; vertical-align: top;
        }
        .dhia-table tr:nth-child(even) td { background-color: #FFFBEB; }
        .dhia-table tr:hover td { background-color: #FEF3C7; }
    </style>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-3 print:hidden">
        <form method="GET" action="{{ route('admin.collection.master-dhia') }}" class="flex flex-wrap gap-3 px-4 py-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cari Nasabah</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Nama nasabah"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 w-44">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Wilayah (KOTA)</label>
                <input type="text" name="wilayah" value="{{ $wilayah }}" placeholder="Filter kota"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 w-36">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Wilayah dari</label>
                <select name="wilayah_from" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <option value=""       @selected(!$wilayahFrom)>Manual</option>
                    <option value="ktp"     @selected($wilayahFrom === 'ktp')>Alamat KTP</option>
                    <option value="jaminan" @selected($wilayahFrom === 'jaminan')>Alamat Jaminan</option>
                    <option value="tinggal" @selected($wilayahFrom === 'tinggal')>Alamat Tinggal</option>
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
            @if($search || $wilayah || $wilayahFrom || $sortBy)
            <a href="{{ route('admin.collection.master-dhia') }}" class="px-4 py-1.5 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">Reset</a>
            @endif
        </form>
    </div>

    <div class="text-xs text-gray-400 mb-2 print:hidden">
        💡 Kolom <strong>NJOP, LT, LB</strong> diambil dari Data Agunan pengajuan pinjaman.
        Kolom <strong>Wilayah</strong>: pilih sumber alamat dari filter "Wilayah dari", atau isi manual melalui Detail nasabah → Catatan Penagihan.
    </div>

    <div class="bg-white border border-amber-300 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="dhia-table border-collapse w-full">
                <thead>
                <tr>
                    <th rowspan="2" style="width:28px">No</th>
                    <th rowspan="2" style="min-width:70px">Tgl<br>Pencairan</th>
                    <th rowspan="2" style="min-width:150px">Nama</th>
                    <th rowspan="2" style="min-width:120px">Nomor Kontrak</th>
                    <th rowspan="2" style="min-width:90px">Plafond</th>
                    <th colspan="2">Jangka Waktu</th>
                    <th rowspan="2" style="min-width:85px">NJOP</th>
                    <th rowspan="2" style="min-width:40px">LT<br>(m²)</th>
                    <th rowspan="2" style="min-width:40px">LB<br>(m²)</th>
                    <th rowspan="2" style="min-width:180px">Alamat KTP</th>
                    <th rowspan="2" style="min-width:180px">Alamat Jaminan</th>
                    <th rowspan="2" style="min-width:180px">Alamat Tinggal Sekarang</th>
                    <th rowspan="2" style="min-width:100px">Wilayah (KOTA)</th>
                    <th rowspan="2" style="min-width:110px">No Tlp</th>
                    <th rowspan="2" style="min-width:140px">Keterangan</th>
                    <th rowspan="2" class="print:hidden" style="min-width:50px">Aksi</th>
                </tr>
                <tr>
                    <th>Tenor</th>
                    <th>Ang Ke</th>
                </tr>
                </thead>
                <tbody>
                @forelse($loans as $i => $loan)
                @php
                    $rec   = $loan->collectionRecord;
                    $noPk  = $loan->application?->notaris_deed_number
                          ?: ('PK-KSP/'.str_pad($loan->id, 5, '0', STR_PAD_LEFT).'/'.date('Y', strtotime($loan->disbursed_date)));

                    $alamatKtp     = $loan->member->address ?? '';
                    $alamatJaminan = $loan->_alamat_jaminan ?: '';
                    $alamatTinggal = $rec?->alamat_tinggal ?: ($loan->_alamat_tinggal ?? $loan->member->address ?? '');

                    // Wilayah: from selected source or manual
                    if ($wilayahFrom === 'ktp') {
                        $wilayahVal = trim((string) last(explode(',', $alamatKtp)));
                    } elseif ($wilayahFrom === 'jaminan') {
                        $wilayahVal = trim((string) last(explode(',', $alamatJaminan)));
                    } elseif ($wilayahFrom === 'tinggal') {
                        $wilayahVal = trim((string) last(explode(',', $alamatTinggal)));
                    } else {
                        $wilayahVal = $rec?->wilayah ?: '';
                    }

                    $njopVal = $loan->_dhia_njop ?? null;
                    $ltVal   = $loan->_dhia_lt   ?? null;
                    $lbVal   = $loan->_dhia_lb   ?? null;
                    $rowNo   = method_exists($loans, 'firstItem') ? $loans->firstItem() + $i : $loop->iteration;
                @endphp
                <tr>
                    <td style="text-align:center;color:#9CA3AF">{{ $rowNo }}</td>
                    <td style="text-align:center">{{ \Carbon\Carbon::parse($loan->disbursed_date)->format('d/m/Y') }}</td>
                    <td><strong>{{ $loan->member->name }}</strong>
                        @if($rec?->is_restruk)<br><span style="font-size:8px;color:#2563EB">[Restruk]</span>@endif
                    </td>
                    <td style="font-size:9px;color:#4B5563">{{ $noPk }}</td>
                    <td style="text-align:right;font-family:monospace">{{ number_format($loan->principal) }}</td>
                    <td style="text-align:center">{{ $loan->tenor_months }}</td>
                    <td style="text-align:center">{{ $loan->_ke }}</td>
                    <td style="text-align:right;font-family:monospace">{{ $njopVal ? number_format($njopVal) : '-' }}</td>
                    <td style="text-align:right">{{ $ltVal ?? '-' }}</td>
                    <td style="text-align:right">{{ $lbVal ?? '-' }}</td>
                    <td style="white-space:normal;font-size:9px">{{ $alamatKtp ?: '-' }}</td>
                    <td style="white-space:normal;font-size:9px">{{ $alamatJaminan ?: '-' }}</td>
                    <td style="white-space:normal;font-size:9px">{{ $alamatTinggal ?: '-' }}</td>
                    <td style="text-align:center;font-weight:600;font-size:9px">{{ $wilayahVal ?: '-' }}</td>
                    <td style="text-align:center;font-size:9px">{{ $loan->member->phone ?? '-' }}</td>
                    <td style="white-space:normal;font-size:9px;color:#6B7280">{{ $rec?->notes ?: '-' }}</td>
                    <td class="print:hidden" style="text-align:center">
                        <a href="{{ route('admin.collection.show', $loan) }}"
                           style="font-size:9px;color:#4F46E5;text-decoration:underline">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" style="text-align:center;padding:24px;color:#9CA3AF">Tidak ada data.</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($loans, 'hasPages') && $loans->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 print:hidden">
            {{ $loans->links() }}
        </div>
        @endif
    </div>

    <p class="text-xs text-gray-400 mt-2 print:hidden">
        Kolom <em>NJOP, LT, LB</em> diambil dari Data Agunan pengajuan pinjaman dan dapat ditimpa melalui halaman
        <a href="{{ route('admin.collection.index') }}" class="underline">Master Tagihan</a> → Detail nasabah → Catatan Penagihan.
    </p>

</x-admin-layout>
