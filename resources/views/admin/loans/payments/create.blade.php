<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman #{{ $loan->id }} / Bayar Angsuran</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kartu Angsuran Anggota</h2>
            </div>
            <a href="{{ route('admin.loans.active.show', $loan) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="space-y-5">

        @if($errors->any())
            <div class="flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="list-disc ml-3 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

            {{-- ===== LEFT: KARTU ANGSURAN ===== --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- Member & Loan Info Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-800 text-white px-6 py-4 text-center">
                        <div class="font-bold text-base tracking-wide">{{ config('app.name', 'KSP') }}</div>
                        <div class="text-xs text-gray-300 mt-0.5">KARTU ANGSURAN ANGGOTA</div>
                    </div>

                    <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-2 text-sm">
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">No Anggota</span>
                                <span class="font-medium text-gray-800">: {{ $loan->member?->member_no ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Nama</span>
                                <span class="font-medium text-gray-800">: {{ $loan->member?->name ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Alamat</span>
                                <span class="font-medium text-gray-800">: {{ $loan->member?->address ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">No. Telepon</span>
                                <span class="font-medium text-gray-800">: {{ $loan->member?->phone ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Jangka Waktu</span>
                                <span class="font-medium text-gray-800">: {{ $loan->tenor_months }} Bulan</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Plafond</span>
                                <span class="font-medium text-gray-800">: Rp {{ number_format($loan->principal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Realisasi</span>
                                <span class="font-medium text-gray-800">: {{ $loan->disbursed_date?->format('d/m/Y') ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Jatuh Tempo</span>
                                <span class="font-medium text-gray-800">: {{ $loan->disbursed_date?->copy()->addMonths($loan->tenor_months)->format('d/m/Y') ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            @php
                                $agunan  = $loan->application?->form_data['agunan']  ?? null;
                                $pasangan = $loan->application?->form_data['pasangan'] ?? null;
                            @endphp
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Jaminan</span>
                                <span class="font-medium text-gray-800">:
                                    @if($agunan)
                                        {{ $agunan['jenis_agunan'] ?? '' }}
                                        @if(!empty($agunan['no_bpkb'])) &nbsp;No. BPKB {{ $agunan['no_bpkb'] }}@endif
                                        @if(!empty($agunan['no_sertifikat'])) &nbsp;SHM {{ $agunan['no_sertifikat'] }}@endif
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Jenis Pinjaman</span>
                                <span class="font-medium text-gray-800">: {{ $loan->loanProduct?->name ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Penjamin</span>
                                <span class="font-medium text-gray-800">: {{ $pasangan['nama'] ?? '-' }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Biaya Cadangan</span>
                                <span class="font-medium text-gray-800">: Rp {{ number_format($loan->insurance_monthly ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex gap-2">
                                <span class="w-32 text-gray-500 shrink-0">Kas Pencairan</span>
                                <span class="font-medium text-gray-800">: {{ $loan->disbursedCashAccount?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Installment Schedule Table --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Jadwal Angsuran</span>
                        <span class="text-xs text-gray-400">{{ $loan->tenor_months }} angsuran</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                    <th class="text-center px-3 py-2.5 w-10">Ke</th>
                                    <th class="text-right px-3 py-2.5">Jumlah Angsuran</th>
                                    <th class="text-center px-3 py-2.5">Tgl Jatuh Tempo</th>
                                    <th class="text-center px-3 py-2.5">Tgl Pembayaran</th>
                                    <th class="text-center px-3 py-2.5">No. Kwitansi</th>
                                    <th class="text-center px-3 py-2.5 w-20">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($schedule as $row)
                                    @php $isPaid = $row['payment'] !== null; $isLate = $row['is_late']; @endphp
                                    <tr class="{{ $isPaid ? ($isLate ? 'bg-orange-50/40' : 'bg-green-50/50') : 'hover:bg-gray-50' }} transition-colors">
                                        <td class="text-center px-3 py-2.5 font-semibold text-gray-700">{{ $row['no'] }}</td>
                                        <td class="text-right px-3 py-2.5 font-medium text-gray-800 tabular-nums">
                                            Rp {{ number_format($row['amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center px-3 py-2.5 text-gray-600 whitespace-nowrap">
                                            {{ $row['due']->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="text-center px-3 py-2.5 text-gray-600 whitespace-nowrap">
                                            @if($isPaid)
                                                @php
                                                    $pd = $row['payment']->trx_date instanceof \Carbon\Carbon
                                                        ? $row['payment']->trx_date
                                                        : \Carbon\Carbon::parse($row['payment']->trx_date);
                                                @endphp
                                                {{ $pd->format('d/m/Y') }}
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center px-3 py-2.5 text-xs text-gray-500 tabular-nums">
                                            @if($isPaid)
                                                KWT-{{ str_pad($row['payment']->id, 5, '0', STR_PAD_LEFT) }}
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center px-3 py-2.5">
                                            @if($isPaid && $isLate)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                                    Terlambat
                                                </span>
                                            @elseif($isPaid)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                                    Lunas
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                                    Belum
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-xs font-semibold text-gray-500">
                                <tr>
                                    <td colspan="2" class="px-3 py-2.5 text-right">
                                        Total Tagihan: <span class="text-gray-800">Rp {{ number_format($loan->total_payable, 0, ',', '.') }}</span>
                                    </td>
                                    <td colspan="2" class="px-3 py-2.5 text-center">
                                        Sudah Dibayar: <span class="text-green-700">Rp {{ number_format($loan->total_paid, 0, ',', '.') }}</span>
                                    </td>
                                    <td colspan="2" class="px-3 py-2.5 text-center">
                                        Sisa: <span class="text-red-600">Rp {{ number_format($loan->outstanding, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- end left --}}

            {{-- ===== RIGHT: PAYMENT FORM ===== --}}
            @php
                $dueDateStr      = $expectedDueDate->toDateString();
                $dailyRateBp     = $penaltyDailyRateBp;
                $earlyRateBp     = $earlyRepaymentRateBp;
                // $installmentAmt already passed from controller (uses outstanding for last slot)
                $outstandingAmt  = (int) $loan->outstanding;
                $accPenaltyTotal = (int) $accPenalty['total'];
                $accOverdue      = (int) $accPenalty['overdue_count'];

                // Build overdue slots for Alpine reactive penalty recalc.
                // paidDate = null  → unpaid (denda accrues up to trxDate, dynamic)
                // paidDate = date  → paid late, penalty not yet collected (fixed denda)
                $overdueSlots = [];
                foreach ($schedule as $row) {
                    $slotDue = $row['due'];
                    $pm      = $row['payment'];
                    if ($pm) {
                        if ($pm->is_early_repayment ?? false) break;
                        $payDate = \Carbon\Carbon::parse($pm->trx_date);
                        if ($payDate->gt($slotDue) && (int)($pm->penalty_amount ?? 0) === 0) {
                            $overdueSlots[] = ['no' => $row['no'], 'due' => $slotDue->toDateString(), 'amount' => $row['amount'], 'paidDate' => $payDate->toDateString()];
                        }
                    } else {
                        $overdueSlots[] = ['no' => $row['no'], 'due' => $slotDue->toDateString(), 'amount' => $row['amount'], 'paidDate' => null];
                    }
                }
            @endphp
            <div class="xl:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-4"
                     x-data="{
                        trxDate:            '{{ now()->toDateString() }}',
                        dueDate:            '{{ $dueDateStr }}',
                        amount:             {{ $installmentAmt }},
                        penaltyAmount:      {{ $accPenaltyTotal }},
                        accOverdue:         {{ $accOverdue }},
                        accDetails:         [],
                        isEarlyRepayment:   false,
                        earlyPenalty:       0,
                        outstanding:        {{ $outstandingAmt }},
                        installmentAmt:     {{ $installmentAmt }},
                        earlyRateBp:        {{ $earlyRateBp }},
                        dailyRateBp:        {{ $dailyRateBp }},
                        overdueSlots:       {{ Js::from($overdueSlots) }},

                        get total() {
                            return this.amount + this.penaltyAmount + (this.isEarlyRepayment ? this.earlyPenalty : 0);
                        },

                        recalcAccPenalty() {
                            if (!this.trxDate) return;
                            const trxDate = new Date(this.trxDate + 'T00:00:00');
                            let total = 0, count = 0;
                            const details = [];
                            for (const slot of this.overdueSlots) {
                                const due = new Date(slot.due + 'T00:00:00');
                                // Unpaid slot not yet due as of trxDate → stop
                                if (!slot.paidDate && due >= trxDate) break;
                                // paid-late → fixed pay date; unpaid → use trxDate
                                const effectiveDate = slot.paidDate
                                    ? new Date(slot.paidDate + 'T00:00:00')
                                    : trxDate;
                                const daysLate = Math.floor((effectiveDate - due) / 86400000);
                                if (daysLate <= 0) continue;
                                const penalty = Math.round(slot.amount * daysLate * (this.dailyRateBp / 10000));
                                total += penalty;
                                count++;
                                details.push({ no: slot.no, daysLate, penalty, amount: slot.amount });
                            }
                            this.penaltyAmount = total;
                            this.accOverdue = count;
                            this.accDetails = details;
                        },

                        recalcEarlyPenalty() {
                            if (this.isEarlyRepayment) {
                                this.amount = this.outstanding;
                                this.earlyPenalty = Math.round(this.outstanding * (this.earlyRateBp / 10000));
                            } else {
                                this.amount = this.installmentAmt;
                                this.earlyPenalty = 0;
                            }
                        },
                     }"
                     x-init="recalcAccPenalty()">

                    <div class="px-5 py-4 border-b border-gray-100">
                        <div class="text-sm font-semibold text-gray-700">Form Pembayaran Angsuran</div>
                        <div class="text-xs text-gray-400 mt-0.5">Pinjaman #{{ $loan->id }} &middot; {{ $loan->member?->name }}</div>
                    </div>

                    <div class="px-5 py-4">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div class="bg-gray-50 rounded-lg px-3 py-2.5">
                                <div class="text-xs text-gray-500">Angsuran/bln</div>
                                <div class="text-sm font-bold text-gray-800 mt-0.5">
                                    Rp {{ number_format($installmentAmt, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-red-50 rounded-lg px-3 py-2.5">
                                <div class="text-xs text-red-500">Sisa Tagihan</div>
                                <div class="text-sm font-bold text-red-600 mt-0.5">
                                    Rp {{ number_format($outstandingAmt, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg px-3 py-2">
                                <div class="text-xs text-blue-500">Jatuh Tempo Angsuran Ini</div>
                                <div class="text-sm font-bold text-blue-700 mt-0.5">
                                    {{ $expectedDueDate->translatedFormat('d F Y') }}
                                </div>
                            </div>
                            <div class="bg-orange-50 rounded-lg px-3 py-2">
                                <div class="text-xs text-orange-500">Denda Belum Dibayar</div>
                                <div class="text-sm font-bold mt-0.5"
                                     :class="penaltyAmount > 0 ? 'text-orange-700' : 'text-gray-400'"
                                     x-text="'Rp ' + penaltyAmount.toLocaleString('id-ID')">
                                </div>
                                <div class="text-xs mt-0.5"
                                     :class="accOverdue > 0 ? 'text-orange-400' : 'text-gray-400'"
                                     x-text="accOverdue > 0 ? accOverdue + ' angsuran tertunggak' : 'Tidak ada tunggakan'">
                                </div>
                            </div>
                        </div>
                        {{-- Total denda already collected across all payments --}}
                        <div class="bg-green-50 rounded-lg px-3 py-2 mb-5 flex items-center justify-between">
                            <div>
                                <div class="text-xs text-green-600 font-medium">Total Denda Sudah Dibayar</div>
                                <div class="text-xs text-green-500 mt-0.5">Akumulasi dari semua kwitansi</div>
                            </div>
                            <div class="text-sm font-bold {{ $totalDendaCollected > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                Rp {{ number_format($totalDendaCollected, 0, ',', '.') }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.loans.payments.store', $loan) }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Tanggal Bayar <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="trx_date"
                                       x-model="trxDate"
                                       @change="recalcAccPenalty()"
                                       value="{{ old('trx_date', now()->toDateString()) }}"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                @error('trx_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Kas Tujuan <span class="text-red-500">*</span>
                                </label>
                                <select name="cash_account_id"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <option value="">Pilih Kas</option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('cash_account_id') == $acc->id)>
                                            {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cash_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Early Repayment --}}
                            <div class="flex items-center gap-2 py-1">
                                <input type="checkbox" id="is_early_repayment" name="is_early_repayment" value="1"
                                       x-model="isEarlyRepayment"
                                       @change="recalcEarlyPenalty()"
                                       class="rounded border-gray-300 text-indigo-600">
                                <label for="is_early_repayment" class="text-sm text-gray-700 cursor-pointer">
                                    Pelunasan Dipercepat (early repayment)
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Jumlah Bayar (Rp) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" inputmode="numeric" data-money-alpine name="amount"
                                       :value="window.formatRupiah(amount)"
                                       @input="amount = parseInt(window.parseRupiah($event.target.value)) || 0"
                                       :readonly="isEarlyRepayment"
                                       :class="isEarlyRepayment ? 'bg-gray-50 text-gray-500' : ''"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                       placeholder="0">
                                <div class="text-xs text-gray-400 mt-1" x-show="!isEarlyRepayment">Default = angsuran/bln, dapat diubah</div>
                                <div class="text-xs text-orange-500 mt-1" x-show="isEarlyRepayment">Otomatis diisi dengan sisa tagihan</div>
                                @error('amount') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            {{-- Denda Akumulasi: always collectable when there are overdue installments --}}
                            <div class="border border-orange-100 bg-orange-50/50 rounded-lg p-3 space-y-2"
                                 x-show="accOverdue > 0">
                                <div class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Denda Keterlambatan</div>
                                {{-- Per-installment breakdown --}}
                                <div class="space-y-0.5" x-show="accDetails.length > 0">
                                    <template x-for="d in accDetails" :key="d.no">
                                        <div class="flex justify-between text-xs text-gray-500">
                                            <span x-text="'Angsuran #' + d.no + ': ' + d.amount.toLocaleString('id-ID') + ' × ' + d.daysLate + ' hari × {{ $dailyRateBp / 100 }}%'"></span>
                                            <span class="text-orange-600 font-medium tabular-nums" x-text="'Rp ' + d.penalty.toLocaleString('id-ID')"></span>
                                        </div>
                                    </template>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">
                                        Total Denda (Rp)
                                    </label>
                                    <input type="text" inputmode="numeric" data-money-alpine name="penalty_amount"
                                           :value="window.formatRupiah(penaltyAmount)"
                                           @input="penaltyAmount = parseInt(window.parseRupiah($event.target.value)) || 0"
                                           placeholder="0"
                                           class="w-full border border-orange-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white">
                                    <div class="text-xs text-gray-400 mt-1">Dihitung dari tanggal bayar, dapat diubah manual</div>
                                </div>
                            </div>

                            {{-- Denda Pelunasan Dipercepat: only for pelunasan --}}
                            <div class="border border-orange-100 bg-orange-50/50 rounded-lg p-3 space-y-2"
                                 x-show="isEarlyRepayment">
                                <div class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Denda Pelunasan Dipercepat</div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">
                                        Denda Dipercepat (Rp)
                                        <span class="text-gray-400">({{ $earlyRepaymentRateBp / 100 }}% dari sisa)</span>
                                    </label>
                                    <input type="text" inputmode="numeric" data-money-alpine name="early_repayment_penalty"
                                           :value="window.formatRupiah(earlyPenalty)"
                                           @input="earlyPenalty = parseInt(window.parseRupiah($event.target.value)) || 0"
                                           placeholder="0"
                                           class="w-full border border-orange-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white">
                                    <div class="text-xs text-gray-400 mt-1">Dapat diubah manual</div>
                                </div>
                            </div>

                            {{-- Hidden fields (single source of truth — no duplicates) --}}
                            {{-- penalty_days = total hari keterlambatan dari semua slot overdue (bukan jumlah slot) --}}
                            <input type="hidden" name="penalty_days" :value="accDetails.reduce((s, d) => s + d.daysLate, 0)">
                            <input type="hidden" name="is_early_repayment" :value="isEarlyRepayment ? 1 : 0">
                            {{-- penalty_amount and early_repayment_penalty submitted via visible inputs above --}}
                            {{-- When accOverdue=0: penalty_amount input hidden but still in DOM with value 0 --}}
                            {{-- When !isEarlyRepayment: earlyPenalty=0, input in DOM submits 0 --}}

                            {{-- Total summary: show whenever there's any denda --}}
                            <div class="bg-gray-50 rounded-lg p-3 space-y-1.5 text-sm"
                                 x-show="accOverdue > 0 || isEarlyRepayment">
                                <div class="flex justify-between">
                                    <span class="text-gray-500" x-text="isEarlyRepayment ? 'Sisa Tagihan (Pelunasan)' : 'Angsuran'"></span>
                                    <span class="font-medium" x-text="'Rp ' + amount.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between" x-show="accOverdue > 0">
                                    <span class="text-gray-500">Denda keterlambatan</span>
                                    <span class="font-medium text-orange-600" x-text="'Rp ' + penaltyAmount.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between" x-show="isEarlyRepayment">
                                    <span class="text-gray-500">Denda pelunasan dipercepat</span>
                                    <span class="font-medium text-orange-600" x-text="'Rp ' + earlyPenalty.toLocaleString('id-ID')"></span>
                                </div>
                                <div class="flex justify-between font-semibold border-t border-gray-200 pt-1.5">
                                    <span class="text-gray-700">Total Diterima</span>
                                    <span class="text-indigo-700" x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Catatan <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                                </label>
                                <input type="text" name="note" value="{{ old('note') }}"
                                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                       maxlength="255" placeholder="Keterangan tambahan">
                                @error('note') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                                    Simpan &amp; Cetak Kwitansi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>{{-- end grid --}}

    </div>
</x-admin-layout>
