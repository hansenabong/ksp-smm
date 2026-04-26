<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif / Kwitansi Biaya-Biaya</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kwitansi Biaya-Biaya — #{{ $loan->id }}
                </h2>
            </div>
            <div class="flex gap-2 print:hidden">
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Kwitansi
                </button>
                <a href="{{ route('admin.loans.active.show', $loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition print:hidden">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-area, #receipt-area * { visibility: visible; }
            #receipt-area { position: fixed; top: 0; left: 0; width: 100%; }
            .print\:hidden { display: none !important; }
        }
    </style>

    @php
        $member = $loan->member;
        $noKwt  = 'KWB-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . now()->format('Y');
        $totalBiaya = collect($items)->sum('jumlah');
    @endphp

    <div class="space-y-5">

        {{-- Input form (non-print) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden print:hidden"
             x-data="{
                rows: {{ json_encode(
                    !empty($items) ? collect($items)->map(fn($i) => ['deskripsi' => $i['deskripsi'], 'jumlah' => $i['jumlah']])->values()->all()
                    : [['deskripsi' => '', 'jumlah' => '']]
                ) }},
                addRow()  { this.rows.push({ deskripsi: '', jumlah: '' }) },
                removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1) },
             }">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Isi Biaya-Biaya Manual</span>
                <span class="text-xs text-gray-400 ml-2">(tambah/hapus baris sesuai kebutuhan)</span>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('admin.loans.active.kwitansi.biaya.print', $loan) }}">
                    @csrf
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase">
                                <th class="pb-2 w-8">#</th>
                                <th class="pb-2">Deskripsi Biaya</th>
                                <th class="pb-2 w-48 text-right">Jumlah (Rp)</th>
                                <th class="pb-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in rows" :key="i">
                                <tr>
                                    <td class="py-1 align-top" x-text="i + 1"></td>
                                    <td class="py-1 pr-2">
                                        <input type="text" :name="'items['+i+'][deskripsi]'" x-model="row.deskripsi"
                                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm"
                                               placeholder="cth: Biaya Notaris" required>
                                    </td>
                                    <td class="py-1 pr-2">
                                        <input type="text" :name="'items['+i+'][jumlah]'" x-model="row.jumlah"
                                               data-money
                                               class="w-full border border-gray-200 rounded px-3 py-2 text-sm text-right font-mono"
                                               placeholder="0" required>
                                    </td>
                                    <td class="py-1 text-center">
                                        <button type="button" @click="removeRow(i)" x-show="rows.length > 1"
                                                class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="mt-3 flex items-center justify-between">
                        <button type="button" @click="addRow()"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Tambah Baris</button>
                        <button type="submit"
                                class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Preview &amp; Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Kwitansi Print Area --}}
        @if(!empty($items))
        <div id="receipt-area" class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header --}}
                <div class="border-b-2 border-gray-800 px-8 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/logo-ksp.jpeg') }}" alt="KSP SMM" class="w-20 h-20 object-contain">
                        <div>
                            <div class="font-bold text-2xl text-gray-800 tracking-wide">KSP Sarana Mitra Mandiri</div>
                            <div class="text-sm text-gray-500 mt-0.5">Koperasi Simpan Pinjam</div>
                            <div class="text-xs text-gray-400 mt-0.5">Jl. RS Fatmawati Komplek Duta Mas B1/16, Jakarta Selatan</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold">No. Kwitansi</div>
                        <div class="text-lg font-bold text-gray-800">{{ $noKwt }}</div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-8 py-6 space-y-4">

                    <div class="text-center">
                        <span class="text-sm font-bold tracking-[0.3em] text-gray-600 uppercase border-b-2 border-gray-800 pb-0.5">
                            K W I T A N S I &nbsp; B I A Y A - B I A Y A
                        </span>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Nama Anggota</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">{{ $member?->name ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">No. Pinjaman</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">#{{ $loan->id }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Tanggal</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">{{ now()->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    {{-- Fee items table --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase w-8">No</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 uppercase">Deskripsi</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-600 uppercase w-48">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $idx => $item)
                                <tr class="border-b border-gray-100">
                                    <td class="px-4 py-2 text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-2 text-gray-800">{{ $item['deskripsi'] }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-800">{{ number_format((int) $item['jumlah'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 border-t-2 border-gray-300">
                                    <td colspan="2" class="px-4 py-3 text-right font-bold text-gray-700 uppercase text-xs tracking-wide">Total Biaya</td>
                                    <td class="px-4 py-3 text-right font-bold font-mono text-gray-900 text-base">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Signatures --}}
                    <div class="pt-8 flex justify-between text-sm text-center">
                        <div>
                            <p class="text-gray-500 mb-16">Peminjam,</p>
                            <div class="border-t border-gray-400 pt-1 px-4 font-semibold text-gray-800">
                                {{ $member?->name ?? '...................................' }}
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-16">Kasir / Treasury,</p>
                            <div class="border-t border-gray-400 pt-1 px-4 font-semibold text-gray-800">
                                ...................................
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif
    </div>
</x-admin-layout>
