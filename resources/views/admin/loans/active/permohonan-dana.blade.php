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

        // Prefer manual bank entry over cash-account fallback
        $bankName = $bank['bank_name'] ?? ($cashType === 'BANK' ? $cashName : '');
        $bankAcNo = $bank['ac_no']     ?? '';
        $bankAtas = $bank['atas_nama'] ?? ($member?->name ?? '');
    @endphp

    @if(session('success'))
        <div class="max-w-3xl mx-auto mb-4 flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm print:hidden">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Editable form (Treasury) — non-print ── --}}
    @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','TREASURY']))
    <div class="max-w-3xl mx-auto mb-5 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden print:hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-700">Isi Data Bank & Hold Angsuran</span>
            <span class="text-xs text-gray-400 ml-2">(manual input oleh Treasury)</span>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('admin.loans.active.permohonan.dana.save', $loan) }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Bank</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $bank['bank_name'] ?? '') }}"
                               placeholder="contoh: Bank BRI"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">A/C No.</label>
                        <input type="text" name="ac_no" value="{{ old('ac_no', $bank['ac_no'] ?? '') }}"
                               placeholder="nomor rekening"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Atas Nama</label>
                        <input type="text" name="atas_nama" value="{{ old('atas_nama', $bank['atas_nama'] ?? '') }}"
                               placeholder="nama pemilik rekening"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div x-data="{
                            ke: {{ old('hold_angsuran_ke', $fees['hold_angsuran_ke'] ?? 0) }},
                            installment: {{ (int) $loan->monthly_installment }},
                            get amount() { return this.ke > 0 ? this.ke * this.installment : 0; },
                            get amountFormatted() {
                                return this.amount.toLocaleString('id-ID');
                            }
                         }" class="col-span-2 grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Hold Angsuran ke-
                            </label>
                            <input type="number" name="hold_angsuran_ke"
                                   x-model.number="ke"
                                   min="0" :max="loan_tenor"
                                   placeholder="0"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <div class="text-xs text-gray-400 mt-1">
                                Angsuran/bln: Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Hold Angsuran (Rp)
                            </label>
                            <input type="text" readonly
                                   :value="amountFormatted"
                                   class="w-full border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-sm text-right font-mono text-gray-600 cursor-not-allowed">
                            <div class="text-xs text-gray-400 mt-1" x-show="ke > 0">
                                <span x-text="ke"></span> × Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}
                            </div>
                        </div>
                        {{-- hidden fallback so form always submits hold_angsuran value --}}
                        <input type="hidden" name="hold_angsuran" :value="amount">
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

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
                            {{-- Right: Dana Yang Diminta --}}
                            <div class="flex-1 space-y-1 text-xs">
                                <div class="font-bold uppercase text-xs mb-1">Dana Yang Diminta</div>
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">Bank</span>
                                    <span>:</span>
                                    <span>{{ $bankName !== '' ? $bankName : '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">A/C No.</span>
                                    <span>:</span>
                                    <span class="border border-gray-600 px-2 py-0.5 font-mono min-w-[100px]">
                                        {{ $bankAcNo }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-20 shrink-0">Atas Nama</span>
                                    <span>:</span>
                                    <span>{{ $bankAtas !== '' ? $bankAtas : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Loan details grid ── --}}
            <div class="border-b border-gray-800">
                <div class="grid grid-cols-2">
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Jenis Fasilitas</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">Pembiayaan</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-l border-gray-400">
                        <span class="w-40 shrink-0 text-xs">Produk</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $loan->loanProduct?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Objek Pembiayaan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $objectFinance }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-l border-gray-400">
                        <span class="w-40 shrink-0 text-xs">No. Anggota</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">{{ $member?->member_no ?? '-' }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Jangka Waktu</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-semibold">{{ $loan->tenor_months }} Bulan</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-l border-gray-400">
                        <span class="w-40 shrink-0 text-xs">Angsuran / Bulan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Suku Bunga</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs">{{ $loan->interest_monthly_percent }}% / bln</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-l border-gray-400">
                        <span class="w-40 shrink-0 text-xs">Bunga (total)</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono">Rp {{ number_format($totalBunga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300">
                        <span class="w-40 shrink-0 text-xs">Nilai Pembiayaan</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono font-semibold">Rp {{ number_format($loan->principal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline px-3 py-1 border-b border-gray-300 border-l border-gray-400">
                        <span class="w-40 shrink-0 text-xs">Pencairan Bersih</span>
                        <span class="mx-1">:</span>
                        <span class="text-xs font-mono font-semibold">Rp {{ number_format(max(0, $pencairanBersih), 0, ',', '.') }}</span>
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
                            <span class="w-36 shrink-0">
                                {{ $feeLabels[$key] }}
                                @if($key === 'hold_angsuran' && !empty($fees['hold_angsuran_ke']))
                                    <span class="text-gray-400">({{ $fees['hold_angsuran_ke'] }} angsuran)</span>
                                @endif
                            </span>
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
            <div class="border-b border-gray-800 px-3 py-2">
                <div class="font-bold text-xs mb-1.5">Pencairan Bersih</div>
                <div class="space-y-1 text-xs pl-4">
                    <div class="flex items-baseline">
                        <span class="w-36 shrink-0">Jumlah potongan</span>
                        <span class="mr-1">: Rp.</span>
                        <span class="font-mono">{{ number_format($jumlahBiaya, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-baseline">
                        <span class="w-36 shrink-0">Jumlah di setor</span>
                        <span class="mr-1">: Rp.</span>
                        <span class="font-bold font-mono">{{ number_format(max(0, $pencairanBersih), 0, ',', '.') }}</span>
                    </div>
                </div>
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
