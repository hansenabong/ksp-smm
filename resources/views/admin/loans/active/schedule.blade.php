<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif / Jadwal Angsuran</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Amortization Schedule — #{{ $loan->id }}
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.loans.active.schedule.pdf', $loan) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak PDF
                </a>
                <a href="{{ route('admin.loans.active.show', $loan) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Info header card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Agreement No.</div>
                        <div class="font-medium text-gray-900">{{ $loan->id }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota</div>
                        <div class="font-medium text-gray-900">{{ $loan->member->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</div>
                        <div class="font-medium text-gray-900">{{ $loan->loanProduct->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal Cair</div>
                        <div class="font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($loan->disbursed_date)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pokok Pinjaman</div>
                        <div class="font-medium text-gray-900">Rp {{ number_format($loan->principal, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tenor</div>
                        <div class="font-medium text-gray-900">{{ $loan->tenor_months }} bulan</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Bunga / Bulan</div>
                        <div class="font-medium text-gray-900">{{ $loan->interest_monthly_percent }}%</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Angsuran / Bulan</div>
                        <div class="font-medium text-gray-900">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-3 h-3 rounded-sm bg-green-100 border border-green-300"></span> Sudah dibayar
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-3 h-3 rounded-sm bg-red-100 border border-red-300"></span> Jatuh tempo (belum dibayar)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-3 h-3 rounded-sm bg-blue-50 border border-blue-200"></span> Subtotal tahunan
                </span>
            </div>

            {{-- Schedule table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="px-3 py-2.5 text-center font-semibold whitespace-nowrap">Per</th>
                            <th class="px-3 py-2.5 text-center font-semibold whitespace-nowrap">Tgl Jatuh Tempo</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Loan Payment</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Loan Income</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Recovery</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Outstanding<br>Principal</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Unearned<br>Loan Income</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">Loan Contract<br>Receivable</th>
                            <th class="px-3 py-2.5 text-right font-semibold whitespace-nowrap">BLV<br>% of Eq.Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $today = \Carbon\Carbon::today();
                            $annualLoanPayment = 0;
                            $annualLoanIncome  = 0;
                            $annualRecovery    = 0;
                            $totalLoanPayment  = 0;
                            $totalLoanIncome   = 0;
                            $totalRecovery     = 0;
                        @endphp

                        @foreach($schedule as $row)
                            @if(!$row['is_row_zero'])
                                @php
                                    $annualLoanPayment += $row['loan_payment'];
                                    $annualLoanIncome  += $row['loan_income'];
                                    $annualRecovery    += $row['recovery'];
                                    $totalLoanPayment  += $row['loan_payment'];
                                    $totalLoanIncome   += $row['loan_income'];
                                    $totalRecovery     += $row['recovery'];
                                @endphp
                            @endif

                            @php
                                $isPaid    = isset($paidMap[$row['per']]);
                                $isOverdue = !$row['is_row_zero'] && !$isPaid && $row['due_date']->lt($today);
                                $rowClass  = $row['is_row_zero']
                                    ? 'bg-gray-50'
                                    : ($isPaid ? 'bg-green-50' : ($isOverdue ? 'bg-red-50' : 'bg-white'));
                            @endphp

                            <tr class="{{ $rowClass }} border-b border-gray-100 hover:brightness-95 transition">
                                <td class="px-3 py-1.5 text-center font-medium text-gray-700">
                                    {{ $row['per'] }}
                                    @if($isPaid)
                                        <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-green-500 align-middle"></span>
                                    @elseif($isOverdue)
                                        <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-red-500 align-middle"></span>
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-center text-gray-600 whitespace-nowrap">
                                    @if($row['is_row_zero'])
                                        {{ $row['due_date']->translatedFormat('d M Y') }}
                                    @else
                                        {{ $row['due_date']->translatedFormat('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-700 font-mono">
                                    @if($row['is_row_zero']) — @else {{ number_format($row['loan_payment'], 2, ',', '.') }} @endif
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-700 font-mono">
                                    @if($row['is_row_zero']) — @else {{ number_format($row['loan_income'], 2, ',', '.') }} @endif
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-700 font-mono">
                                    @if($row['is_row_zero']) — @else {{ number_format($row['recovery'], 2, ',', '.') }} @endif
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-800 font-mono font-medium">
                                    {{ number_format($row['outstanding_principal'], 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-700 font-mono">
                                    {{ number_format($row['unearned_loan_income'], 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-800 font-mono font-medium">
                                    {{ number_format($row['loan_contract_receivable'], 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-gray-700 font-mono">
                                    {{ number_format($row['blv_pct'], 4, ',', '.') }}
                                </td>
                            </tr>

                            {{-- Annual subtotal after every 12th installment --}}
                            @if(!$row['is_row_zero'] && $row['per'] % 12 === 0)
                                <tr class="bg-blue-50 border-b-2 border-blue-200 font-semibold">
                                    <td colspan="2" class="px-3 py-1.5 text-center text-blue-700 text-xs uppercase tracking-wide">
                                        Subtotal Tahun ke-{{ intdiv($row['per'], 12) }}
                                    </td>
                                    <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualLoanPayment, 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualLoanIncome, 2, ',', '.') }}</td>
                                    <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualRecovery, 2, ',', '.') }}</td>
                                    <td colspan="4"></td>
                                </tr>
                                @php $annualLoanPayment = $annualLoanIncome = $annualRecovery = 0; @endphp
                            @endif
                        @endforeach

                        {{-- Trailing partial-year subtotal if tenor is not multiple of 12 --}}
                        @if($loan->tenor_months % 12 !== 0 && $annualLoanPayment > 0)
                            <tr class="bg-blue-50 border-b-2 border-blue-200 font-semibold">
                                <td colspan="2" class="px-3 py-1.5 text-center text-blue-700 text-xs uppercase tracking-wide">
                                    Subtotal Tahun ke-{{ ceil($loan->tenor_months / 12) }}
                                </td>
                                <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualLoanPayment, 2, ',', '.') }}</td>
                                <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualLoanIncome, 2, ',', '.') }}</td>
                                <td class="px-3 py-1.5 text-right text-blue-800 font-mono">{{ number_format($annualRecovery, 2, ',', '.') }}</td>
                                <td colspan="4"></td>
                            </tr>
                        @endif

                        {{-- Grand total --}}
                        <tr class="bg-gray-800 text-white font-bold">
                            <td colspan="2" class="px-3 py-2 text-center text-xs uppercase tracking-wide">Total</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($totalLoanPayment, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($totalLoanIncome, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($totalRecovery, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-mono">0,00</td>
                            <td class="px-3 py-2 text-right font-mono">0,00</td>
                            <td class="px-3 py-2 text-right font-mono">0,00</td>
                            <td class="px-3 py-2 text-right font-mono">0,0000</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-admin-layout>
