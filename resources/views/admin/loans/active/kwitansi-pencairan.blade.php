<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif / Kwitansi Pencairan</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kwitansi Pencairan — #{{ $loan->id }}
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

    {{-- Print styles --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-area, #receipt-area * { visibility: visible; }
            #receipt-area { position: fixed; top: 0; left: 0; width: 100%; }
            .print\:hidden { display: none !important; }
        }
    </style>

    @php
        $member  = $loan->member;
        $tgl     = $loan->disbursed_date instanceof \Carbon\Carbon
                    ? $loan->disbursed_date
                    : \Carbon\Carbon::parse($loan->disbursed_date);

        $feeLabels = [
            'biaya_administrasi' => 'Biaya Administrasi',
            'biaya_provisi'      => 'Biaya Provisi',
            'biaya_notaris'      => 'Biaya Notaris',
            'asuransi'           => 'Asuransi',
            'pengecekan'         => 'Pengecekan',
            'ploting'            => 'Ploting',
            'roya'               => 'Roya',
            'pnpb'               => 'PNPB',
            'hold_imb'           => 'Hold IMB',
            'hold_pbb'           => 'Hold PBB',
            'hold_angsuran'      => 'Hold Angsuran',
            'bphtb'              => 'BPHTB',
            'pph'                => 'PPh',
        ];

        $jumlahBiaya = collect($fees)->sum();
        $netPencairan = (int)$loan->principal - $jumlahBiaya;
        $noKwt = 'KWP-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT);

        $words = ucwords(terbilang((int)$loan->principal));
        $wordsNet = ucwords(terbilang(max(0, $netPencairan)));
    @endphp

    <div class="space-y-5">

        @if(session('success'))
            <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm print:hidden">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Edit fees form (non-print) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden print:hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-700">Isi Biaya-Biaya Pencairan</span>
                <span class="text-xs text-gray-400 ml-2">(kosongkan jika tidak ada)</span>
            </div>
            <div class="p-5">
                <form method="POST" action="{{ route('admin.loans.active.kwitansi.pencairan.save', $loan) }}">
                    @csrf
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($feeLabels as $key => $label)
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                {{ $label }}
                            </label>
                            <input type="text" name="{{ $key }}"
                                   value="{{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '' }}"
                                   placeholder="0"
                                   x-data
                                   @input="$el.value = $el.value.replace(/[^0-9]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-right font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                        </div>
                        @endforeach
                    </div>
                    @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','TREASURY']))
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Simpan Biaya
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Kwitansi --}}
        <div id="receipt-area" class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header --}}
                <div class="border-b-2 border-gray-800 px-8 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-ksp.jpeg') }}" alt="KSP SMM" class="w-14 h-14 object-contain">
                        <div>
                            <div class="font-bold text-xl text-gray-800 tracking-wide">KSP Sarana Mitra Mandiri</div>
                            <div class="text-xs text-gray-500 mt-0.5">Koperasi Simpan Pinjam</div>
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
                            K W I T A N S I &nbsp; P E N C A I R A N
                        </span>
                    </div>

                    {{-- Penerima info --}}
                    <div class="space-y-2.5 text-sm">
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Telah Diterima Oleh</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">{{ $member?->name ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">No. Anggota</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">{{ $member?->member_no ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Produk Pinjaman</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">{{ $loan->loanProduct?->name ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Plafon Pinjaman</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">
                                Rp {{ number_format($loan->principal, 0, ',', '.') }}
                                <span class="text-xs font-normal text-gray-400 block mt-0.5">
                                    ({{ $words }} Rupiah)
                                </span>
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Tenor / Angsuran</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">
                                {{ $loan->tenor_months }} Bulan &nbsp;&middot;&nbsp;
                                Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}/bln
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Tanggal Pencairan</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">{{ $tgl->translatedFormat('d F Y') }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-44 text-gray-500 shrink-0">Kas Pencairan</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">{{ $loan->disbursedCashAccount?->name ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Biaya-Biaya Lainnya --}}
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wide">Biaya-Biaya Lainnya</span>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-sm">
                                @php
                                    $leftKeys  = ['biaya_administrasi','biaya_provisi','biaya_notaris','asuransi','pengecekan','ploting','roya','pnpb','hold_imb','hold_pbb'];
                                    $rightKeys = ['hold_angsuran','bphtb','pph'];
                                @endphp

                                <div class="space-y-1.5">
                                    @foreach($leftKeys as $key)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">{{ $feeLabels[$key] }}</span>
                                        <span class="font-mono text-gray-800">
                                            Rp {{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '&mdash;' }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="space-y-1.5">
                                    @foreach($rightKeys as $key)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-600">{{ $feeLabels[$key] }}</span>
                                        <span class="font-mono text-gray-800">
                                            Rp {{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '&mdash;' }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Jumlah Biaya --}}
                            <div class="mt-3 pt-2.5 border-t border-gray-300 flex items-center justify-end gap-4">
                                <span class="text-sm font-bold text-gray-700">Jumlah Biaya</span>
                                <span class="text-sm font-bold text-gray-800 font-mono">
                                    Rp {{ number_format($jumlahBiaya, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Net disbursement box --}}
                    <div class="border-2 border-gray-800 rounded-lg px-5 py-3 flex items-center justify-between bg-gray-50">
                        <div>
                            <div class="text-sm font-semibold text-gray-700">Dana Yang Diterima</div>
                            @if($jumlahBiaya > 0)
                            <div class="text-xs text-gray-400 mt-0.5">
                                ({{ $wordsNet }} Rupiah)
                            </div>
                            @endif
                        </div>
                        <span class="text-2xl font-bold text-gray-800 tabular-nums">
                            Rp {{ number_format(max(0, $netPencairan), 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Signatures --}}
                    <div class="grid grid-cols-3 gap-6 pt-2">
                        <div class="text-center text-sm">
                            <div class="text-gray-500 text-xs mb-14">Peminjam,</div>
                            <div class="border-t border-gray-400 pt-1 font-semibold text-gray-700">{{ $member?->name ?? '_______________' }}</div>
                        </div>
                        <div class="text-center text-sm">
                            <div class="text-gray-500 text-xs mb-14">Mengetahui,</div>
                            <div class="border-t border-gray-400 pt-1 font-semibold text-gray-700">_______________</div>
                        </div>
                        <div class="text-center text-sm">
                            <div class="text-gray-500 text-xs mb-14">Kasir / Petugas,</div>
                            <div class="border-t border-gray-400 pt-1 font-semibold text-gray-700">{{ $loan->creator?->name ?? '_______________' }}</div>
                        </div>
                    </div>

                    {{-- Footer note --}}
                    <div class="border-t border-gray-100 pt-3 text-xs text-gray-400 space-y-0.5">
                        <p>* Kwitansi ini merupakan bukti pencairan pinjaman yang sah.</p>
                        <p>* Simpan kwitansi ini sebagai bukti penerimaan dana.</p>
                    </div>

                </div>{{-- end body --}}

            </div>{{-- end card --}}
        </div>{{-- end receipt-area --}}

    </div>
</x-admin-layout>
