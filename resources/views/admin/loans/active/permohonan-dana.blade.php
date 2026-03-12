<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif / Permohonan Dana</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Permohonan Dana — #{{ $loan->id }}
                </h2>
            </div>
            <div class="flex gap-2 print:hidden">
                <a href="{{ route('admin.loans.active.permohonan.dana.pdf', $loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Unduh PDF
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak
                </button>
                <a href="{{ route('admin.loans.active.show', $loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            body * { visibility: hidden; }
            #form-area, #form-area * { visibility: visible; }
            #form-area { position: fixed; top: 0; left: 0; width: 100%; }
            .print\:hidden { display: none !important; }
        }
    </style>

    @php
        $member  = $loan->member;
        $tgl     = $loan->disbursed_date instanceof \Carbon\Carbon
                    ? $loan->disbursed_date
                    : \Carbon\Carbon::parse($loan->disbursed_date);

        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . $tgl->format('Y'));

        $objectFinance = $loan->application?->object_finance ?? '-';

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

        $leftFeeKeys  = ['biaya_administrasi','biaya_provisi','biaya_notaris','asuransi','pengecekan','ploting','roya','pnpb','hold_imb','hold_pbb'];
        $rightFeeKeys = ['hold_angsuran','bphtb','pph'];

        $jumlahBiaya  = collect($fees)->sum();
        $pencairanBersih = (int)$loan->principal - $jumlahBiaya;

        $totalBunga   = (int)$loan->interest_monthly * (int)$loan->tenor_months;
        $appName      = config('app.name', 'KSP-SMM');

        $cashName = $loan->disbursedCashAccount?->name ?? '-';
        $cashType = $loan->disbursedCashAccount?->type ?? 'CASH'; // CASH or BANK
    @endphp

    <div id="form-area" class="max-w-3xl mx-auto">
        <div class="bg-white border border-gray-800 text-sm" style="font-family: Arial, sans-serif;">

            {{-- ── Title ── --}}
            <div class="border-b border-gray-800 py-2 text-center">
                <div class="font-bold text-base uppercase tracking-wide">{{ strtoupper($appName) }}</div>
                <div class="font-bold text-sm uppercase tracking-wider">Permohonan Dana</div>
            </div>

            {{-- ── Nasabah + No/Date ── --}}
            <div class="flex border-b border-gray-800">
                <div class="flex-1 flex items-center gap-2 px-3 py-2 border-r border-gray-800">
                    <span class="font-bold uppercase whitespace-nowrap">Nasabah</span>
                    <span class="mx-1">:</span>
                    <span class="font-semibold">{{ $member?->name ?? '-' }}</span>
                </div>
                <div class="w-48 shrink-0">
                    <div class="flex border-b border-gray-800">
                        <div class="w-14 px-2 py-1 font-semibold border-r border-gray-800">No</div>
                        <div class="flex-1 px-2 py-1 font-mono text-xs">{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</div>
                    </div>
                    <div class="flex">
                        <div class="w-14 px-2 py-1 font-semibold border-r border-gray-800">Date</div>
                        <div class="flex-1 px-2 py-1 text-xs">{{ $tgl->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- ── PK / Pembiayaan / Pembayaran ── --}}
            <div class="border-b border-gray-800">
                <div class="flex items-baseline border-b border-gray-400 px-3 py-1">
                    <span class="w-56 font-bold uppercase underline shrink-0">No. Perjanjian Kredit</span>
                    <span class="mx-2">:</span>
                    <span class="font-mono text-xs">{{ $noPk }}</span>
                </div>
                <div class="flex items-baseline border-b border-gray-400 px-3 py-1">
                    <span class="w-56 font-bold uppercase underline shrink-0">Pembiayaan Yang Disetujui</span>
                    <span class="mx-2">:</span>
                    <span class="font-mono">Rp {{ number_format($loan->principal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-start px-3 py-1">
                    <span class="w-56 font-bold uppercase underline shrink-0 pt-1">Pembayaran</span>
                    <span class="mx-2 pt-1">:</span>

                    {{-- Dana yang diminta sub-block --}}
                    <div class="flex-1">
                        <div class="flex gap-6">
                            {{-- Left: Amount --}}
                            <div class="flex-1 space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-24 shrink-0 text-xs font-semibold">Amount</span>
                                    <span>:</span>
                                    <span class="flex-1 border border-gray-700 px-2 py-0.5 font-mono text-xs min-w-[100px]">
                                        Rp {{ number_format($loan->principal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            {{-- Right: Bank/AC/Atas Nama --}}
                            <div class="flex-1 space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">Bank</span>
                                    <span>:</span>
                                    <span>{{ $cashType === 'BANK' ? $cashName : '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">A/C No.</span>
                                    <span>:</span>
                                    <span class="border border-gray-600 px-2 py-0.5 font-mono min-w-[100px]">
                                        {{ $cashType === 'BANK' ? $cashName : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">Atas Nama</span>
                                    <span>:</span>
                                    <span>{{ $member?->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-1.5 pt-1 border-t border-gray-300 text-xs text-gray-500 italic">Dana Yang Diminta</div>
                    </div>
                </div>
            </div>

            {{-- ── Loan details grid ── --}}
            <div class="border-b border-gray-800">
                <div class="grid grid-cols-2">
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Produk</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $loan->loanProduct?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-r border-gray-800">
                        <span class="w-40 shrink-0 text-xs">Objek Pembiayaan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $objectFinance }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">No. Anggota</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">{{ $member?->member_no ?? '-' }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-r border-gray-800">
                        <span class="w-40 shrink-0 text-xs">Jangka Waktu</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-semibold">{{ $loan->tenor_months }} Bulan</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Angsuran / Bulan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-r border-gray-800">
                        <span class="w-40 shrink-0 text-xs">Bunga</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">Rp {{ number_format($totalBunga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Suku Bunga</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $loan->interest_monthly_percent }}% / bln</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 col-span-2">
                        <span class="w-40 shrink-0 text-xs">Nilai Pembiayaan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono font-semibold">Rp {{ number_format($loan->principal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- ── Biaya-Biaya Lainnya ── --}}
            <div class="border-b border-gray-800 px-3 py-2">
                <div class="font-bold text-xs uppercase mb-2">Biaya-Biaya Lainnya</div>
                <div class="grid grid-cols-2 gap-x-8 text-xs">
                    <div class="space-y-1">
                        @foreach($leftFeeKeys as $key)
                        <div class="flex items-baseline">
                            <span class="w-36 shrink-0">{{ $feeLabels[$key] }}</span>
                            <span class="mr-1">: Rp.</span>
                            <span class="font-mono flex-1 text-right pr-4">
                                {{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <div class="space-y-1">
                        @foreach($rightFeeKeys as $key)
                        <div class="flex items-baseline">
                            <span class="w-36 shrink-0">{{ $feeLabels[$key] }}</span>
                            <span class="mr-1">: Rp</span>
                            <span class="font-mono flex-1 text-right pr-4">
                                {{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Jumlah Biaya --}}
                <div class="flex justify-end mt-2 pt-1.5 border-t border-gray-400">
                    <div class="flex items-baseline gap-4">
                        <span class="font-bold text-xs">Jumlah Biaya</span>
                        <span class="text-xs">: Rp.</span>
                        <span class="font-bold font-mono text-xs min-w-[100px] text-right">
                            {{ number_format($jumlahBiaya, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── Pencairan Bersih ── --}}
            <div class="flex items-baseline border-b border-gray-800 px-3 py-2">
                <span class="font-bold text-xs w-36 shrink-0">Pencairan Bersih</span>
                <span class="text-xs mr-1">: Rp.</span>
                <span class="font-bold font-mono text-sm">{{ number_format(max(0, $pencairanBersih), 0, ',', '.') }}</span>
            </div>

            {{-- ── Signature table ── --}}
            <div class="grid grid-cols-4 text-xs" style="min-height: 90px;">
                <div class="border-r border-gray-800 px-2 py-2 text-center">
                    &nbsp;
                </div>
                <div class="border-r border-gray-800 px-2 py-2 text-center">
                    <div class="font-semibold">Di buat oleh</div>
                    <div class="mt-10 border-t border-gray-700 pt-1">{{ $loan->creator?->name ?? '_______________' }}</div>
                </div>
                <div class="border-r border-gray-800 px-2 py-2 text-center">
                    <div class="font-semibold">Diperiksa oleh</div>
                    <div class="mt-10 border-t border-gray-700 pt-1">&nbsp;</div>
                </div>
                <div class="px-2 py-2 text-center">
                    <div class="font-semibold">Disetujui Oleh</div>
                    <div class="mt-10 border-t border-gray-700 pt-1">&nbsp;</div>
                </div>
            </div>

        </div>{{-- end form card --}}

        {{-- Hint for fee editing (non-print) --}}
        @if(empty($fees))
        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 print:hidden">
            Biaya-biaya belum diisi. Isi terlebih dahulu melalui
            <a href="{{ route('admin.loans.active.kwitansi.pencairan', $loan) }}"
               class="font-semibold underline">Kwitansi Pencairan</a>.
        </div>
        @endif

    </div>
</x-admin-layout>
