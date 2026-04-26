<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif / Kwitansi Plafon</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kwitansi Plafon — #{{ $loan->id }}
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
        $amount  = (int) $loan->principal;
        $words   = ucwords(terbilang($amount));

        // No. PK — prefer notaris deed number, fallback to formatted loan ID
        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . $tgl->format('Y'));

        $noKwt = 'KWL-' . str_pad($loan->id, 5, '0', STR_PAD_LEFT);

        $appName = 'KSP SMM';
    @endphp

    <div class="space-y-5">

        {{-- Kwitansi --}}
        <div id="receipt-area" class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

                {{-- Outer layout: left strip + right content --}}
                <div class="flex min-h-[320px]">

                    {{-- Left company strip --}}
                    <div class="w-36 bg-gray-900 flex flex-col items-center justify-center py-6 px-3 shrink-0">
                        {{-- Vertical company name --}}
                        <div class="text-white font-bold text-sm tracking-widest uppercase whitespace-nowrap"
                             style="writing-mode: vertical-rl; transform: rotate(180deg); letter-spacing: 0.15em;">
                            {{ $appName }}
                        </div>
                        <div class="mt-4 w-20 h-20 flex items-center justify-center">
                            <img src="{{ asset('images/logo-ksp.jpeg') }}" alt="KSP SMM" class="w-20 h-20 object-contain rounded-full border-2 border-white/40">
                        </div>
                        <div class="mt-3 text-white/60 text-xs text-center" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 9px; letter-spacing: 0.1em;">
                            Koperasi Simpan Pinjam
                        </div>
                    </div>

                    {{-- Right receipt content --}}
                    <div class="flex-1 px-8 py-6">

                        {{-- Top row: No + KWITANSI --}}
                        <div class="flex items-start justify-between mb-5">
                            <div>
                                <span class="text-sm text-gray-600">No : </span>
                                <span class="text-xl font-bold text-gray-800 tracking-wide">{{ $noKwt }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-extrabold tracking-[0.25em] text-gray-800 uppercase">K W I T A N S I</div>
                            </div>
                        </div>

                        {{-- Receipt fields --}}
                        <div class="space-y-3 text-sm">

                            {{-- Sudah terima dari --}}
                            <div class="flex gap-2 items-baseline">
                                <span class="w-40 text-gray-600 shrink-0">Sudah terima dari</span>
                                <span class="text-gray-500 shrink-0">:</span>
                                <span class="flex-1 font-semibold text-gray-800 border-b border-gray-300 pb-0.5">
                                    {{ $appName }}
                                </span>
                            </div>

                            {{-- Banyaknya Uang --}}
                            <div class="flex gap-2 items-baseline">
                                <span class="w-40 text-gray-600 shrink-0">Banyaknya Uang</span>
                                <span class="text-gray-500 shrink-0">:</span>
                                <span class="flex-1 font-bold text-gray-800 border-b border-gray-300 pb-0.5 bg-yellow-50 px-2">
                                    ** {{ $words }} Rupiah **
                                </span>
                            </div>

                            {{-- Untuk Pembayaran --}}
                            <div class="flex gap-2 items-start">
                                <span class="w-40 text-gray-600 shrink-0">Untuk Pembayaran</span>
                                <span class="text-gray-500 shrink-0">:</span>
                                <div class="flex-1 border border-gray-200 rounded-md p-3 bg-gray-50 space-y-1.5 text-sm">
                                    <div class="font-semibold text-gray-700">Pembiayaan Konsumen :</div>
                                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-gray-700">
                                        <span>PK. No. <strong>{{ $noPk }}</strong></span>
                                        <span class="text-gray-400">a/n :</span>
                                        <strong>{{ $member?->name ?? '-' }}</strong>
                                    </div>
                                    <div class="flex items-baseline gap-2 text-gray-700 pt-1">
                                        <span class="flex-1 border-b border-dotted border-gray-400">Sebesar</span>
                                        <span class="font-semibold whitespace-nowrap">Rp &nbsp; {{ number_format($amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Amount box + Date --}}
                        <div class="flex items-start justify-between gap-6 mt-5">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-700">Rp.</span>
                                <div class="border-2 border-gray-700 px-5 py-2 min-w-[160px] text-center">
                                    <span class="text-lg font-bold tabular-nums text-gray-800">
                                        {{ number_format($amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right text-sm text-gray-600">
                                <div>
                                    {{ $tgl->translatedFormat('d F Y') }}
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="mt-5 text-sm text-gray-600">
                            <div class="mb-1">Keterangan :</div>
                            <div class="border-b border-gray-300 mb-2 pb-2 min-h-[20px]">
                                {{ $loan->loanProduct?->name ?? '' }}
                                &middot; Tenor {{ $loan->tenor_months }} bulan
                                &middot; Angsuran Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}/bln
                            </div>
                            <div class="border-b border-gray-300 min-h-[20px]">&nbsp;</div>
                        </div>

                        {{-- Signature + Meterai --}}
                        <div class="flex items-end justify-between mt-6">
                            <div class="text-xs text-gray-400">
                                <span>No. Anggota: {{ $member?->member_no ?? '-' }}</span>
                            </div>
                            <div class="text-center">
                                {{-- Meterai placeholder --}}
                                <div class="w-24 h-20 border-2 border-dashed border-gray-300 rounded flex flex-col items-center justify-center mb-1 mx-auto">
                                    <div class="text-xs text-gray-400 text-center leading-tight px-1">Meterai<br>Tempel</div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 border-t border-gray-400 pt-1 w-36 text-center">
                                    ( {{ $member?->name ?? '_______________' }} )
                                </div>
                            </div>
                        </div>

                    </div>{{-- end right content --}}
                </div>{{-- end flex --}}

            </div>{{-- end card --}}
        </div>{{-- end receipt-area --}}

        {{-- Info strip (non-print) --}}
        <div class="max-w-3xl mx-auto grid grid-cols-3 gap-3 print:hidden">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                <div class="text-xs text-gray-500">No PK</div>
                <div class="text-sm font-semibold text-gray-700 mt-0.5 font-mono">{{ $noPk }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                <div class="text-xs text-gray-500">Plafon Pinjaman</div>
                <div class="text-base font-bold text-indigo-600 mt-0.5">Rp {{ number_format($amount, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                <div class="text-xs text-gray-500">Atas Nama</div>
                <div class="text-sm font-semibold text-gray-700 mt-0.5">{{ $member?->name ?? '-' }}</div>
            </div>
        </div>

    </div>
</x-admin-layout>
