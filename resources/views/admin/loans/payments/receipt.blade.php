<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pembayaran Angsuran / Kwitansi</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Kwitansi #KWT-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}
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
                <a href="{{ route('admin.loans.active.show', $payment->loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Lihat Detail Pinjaman
                </a>
                <a href="{{ route('admin.loans.payments.create', $payment->loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Bayar Angsuran Lagi
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
        $loan    = $payment->loan;
        $member  = $loan->member;
        $amount  = (int) $payment->amount;
        $penaltyAmt = (int) ($payment->penalty_amount ?? 0);
        $earlyPenalty = (int) ($payment->early_repayment_penalty ?? 0);
        $isEarlyRepayment = (bool) $payment->is_early_repayment;
        $totalCash = $amount + $penaltyAmt + $earlyPenalty;
        $tgl     = $payment->trx_date instanceof \Carbon\Carbon
                    ? $payment->trx_date
                    : \Carbon\Carbon::parse($payment->trx_date);
        $noKwt   = 'KWT-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT);

        // terbilang — gunakan total cash jika ada denda
        $hasPenalty = $penaltyAmt > 0 || $earlyPenalty > 0;
        $words = ucwords(terbilang($hasPenalty ? $totalCash : $amount));
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

        {{-- Kwitansi Card --}}
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
                <div class="px-8 py-6 space-y-5">

                    {{-- KWITANSI label --}}
                    <div class="text-center">
                        <span class="text-sm font-bold tracking-[0.3em] text-gray-600 uppercase border-b-2 border-gray-800 pb-0.5">
                            K W I T A N S I
                        </span>
                    </div>

                    {{-- Receipt fields --}}
                    <div class="space-y-3 text-sm">
                        <div class="flex gap-3">
                            <span class="w-40 text-gray-500 shrink-0">Sudah Terima Dari</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">{{ $member?->name ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-40 text-gray-500 shrink-0">No Anggota</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-medium text-gray-700">{{ $member?->member_no ?? '-' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-40 text-gray-500 shrink-0">Banyaknya Uang</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <span class="font-semibold text-gray-800">
                                <span class="bg-yellow-50 border border-yellow-200 rounded px-2 py-0.5">
                                    ** {{ $words }} Rupiah **
                                </span>
                            </span>
                        </div>
                        <div class="flex gap-3">
                            <span class="w-40 text-gray-500 shrink-0">Untuk Pembayaran</span>
                            <span class="text-gray-400 shrink-0">:</span>
                            <div class="text-gray-700">
                                <div>
                                    {{ $isEarlyRepayment ? 'Pelunasan' : 'Angsuran' }} Pinjaman No. #{{ $loan->id }}
                                    @if($installmentNo && !$isEarlyRepayment)
                                        <span class="text-gray-500">— Pembayaran ke-{{ $installmentNo }} dari {{ $totalTenor }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $loan->loanProduct?->name ?? '-' }}
                                    &middot; Plafond Rp {{ number_format($loan->principal, 0, ',', '.') }}
                                    &middot; {{ $loan->tenor_months }} Bulan
                                </div>
                                @if($payment->note)
                                    <div class="text-xs text-gray-400 mt-0.5">Ket: {{ $payment->note }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Amount box --}}
                    @if($hasPenalty)
                    <div class="border border-gray-200 rounded-lg px-5 py-3 bg-gray-50 space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ $isEarlyRepayment ? 'Pelunasan Pokok + Bunga' : 'Angsuran' }}</span>
                            <span class="font-medium text-gray-800 tabular-nums">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                        </div>
                        @if($penaltyAmt > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Denda Keterlambatan</span>
                            <span class="font-medium text-orange-700 tabular-nums">Rp {{ number_format($penaltyAmt, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($earlyPenalty > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Denda Pelunasan Dipercepat</span>
                            <span class="font-medium text-orange-700 tabular-nums">Rp {{ number_format($earlyPenalty, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between border-t-2 border-gray-800 pt-2 mt-2">
                            <span class="text-gray-800 font-bold">Total Diterima</span>
                            <span class="text-2xl font-bold text-gray-800 tabular-nums">Rp {{ number_format($totalCash, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @else
                    <div class="border-2 border-gray-800 rounded-lg px-5 py-3 flex items-center justify-between bg-gray-50">
                        <span class="text-sm text-gray-600 font-semibold">Jumlah</span>
                        <span class="text-2xl font-bold text-gray-800 tabular-nums">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </span>
                    </div>
                    @endif

                    {{-- Date + Kas --}}
                    <div class="flex items-center gap-6 text-sm text-gray-500">
                        <div>
                            <span class="text-gray-400">Tanggal :</span>
                            <span class="ml-1 font-medium text-gray-700">{{ $tgl->translatedFormat('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Kas :</span>
                            <span class="ml-1 font-medium text-gray-700">{{ $payment->cashAccount?->name ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Signatures --}}
                    <div class="grid grid-cols-2 gap-8 pt-2">
                        <div class="text-center text-sm">
                            <div class="text-gray-500 text-xs mb-16">Peminjam,</div>
                            <div class="border-t border-gray-400 pt-1 font-semibold text-gray-700">{{ $member?->name ?? '_______________' }}</div>
                        </div>
                        <div class="text-center text-sm">
                            <div class="text-gray-500 text-xs mb-16">Petugas / Kasir,</div>
                            <div class="border-t border-gray-400 pt-1 font-semibold text-gray-700">{{ $payment->creator?->name ?? '_______________' }}</div>
                        </div>
                    </div>

                    {{-- Footer note --}}
                    <div class="border-t border-gray-100 pt-3 text-xs text-gray-400 space-y-0.5">
                        <p>* Kwitansi ini merupakan bukti pembayaran yang sah.</p>
                        <p>* Simpan kwitansi ini dengan baik sebagai bukti pembayaran.</p>
                    </div>

                </div>{{-- end body --}}

            </div>{{-- end card --}}

            {{-- Sisa tagihan info (tidak tercetak) --}}
            <div class="mt-4 grid grid-cols-3 gap-3 print:hidden">
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                    <div class="text-xs text-gray-500">Dibayar kali ini</div>
                    <div class="text-base font-bold text-indigo-600 mt-0.5">Rp {{ number_format($hasPenalty ? $totalCash : $amount, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                    <div class="text-xs text-gray-500">Total Sudah Dibayar</div>
                    <div class="text-base font-bold text-green-600 mt-0.5">Rp {{ number_format($loan->total_paid, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 text-center">
                    <div class="text-xs {{ $loan->outstanding > 0 ? 'text-red-500' : 'text-gray-500' }}">Sisa Tagihan</div>
                    <div class="text-base font-bold {{ $loan->outstanding > 0 ? 'text-red-600' : 'text-gray-500' }} mt-0.5">
                        Rp {{ number_format($loan->outstanding, 0, ',', '.') }}
                        @if($loan->outstanding === 0)
                            <span class="text-xs font-normal text-green-500">(LUNAS)</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- end receipt-area --}}

    </div>
</x-admin-layout>
