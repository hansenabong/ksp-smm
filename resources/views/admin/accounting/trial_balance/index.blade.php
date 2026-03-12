<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Neraca Saldo (Trial Balance)</h2>
            <div class="flex gap-2 print:hidden">
            <a href="{{ route('admin.accounting.trial_balance.export', array_filter(['date_from'=>$dateFrom,'date_to'=>$dateTo])) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                ↓ Export Excel
            </a>
            <button onclick="window.print()"
                    class="print:hidden inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak PDF
            </button>
            </div>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } }</style>

    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div class="flex items-center gap-2 pb-0.5">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="show_zero" value="1" @checked($showZero)
                               class="rounded border-gray-300">
                        Tampilkan akun saldo nol
                    </label>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.accounting.trial_balance.index') }}"
                       class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Balance status banners --}}
        @if($isBalanced)
            <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Neraca Saldo <strong>seimbang</strong> — Total Debit = Total Kredit pada kolom Mutasi dan Saldo Akhir.
            </div>
        @else
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm space-y-1">
                <div class="flex items-center gap-2 font-semibold">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Neraca Saldo tidak seimbang — periksa jurnal yang belum terposting atau ada entri yang rusak.
                </div>
                @if(!$periodBalanced)
                    <div class="text-xs">Mutasi Periode: Debit Rp {{ number_format($totalPeriodD, 0, ',', '.') }} ≠ Kredit Rp {{ number_format($totalPeriodC, 0, ',', '.') }} (Selisih: Rp {{ number_format(abs($totalPeriodD - $totalPeriodC), 0, ',', '.') }})</div>
                @endif
                @if(!$endingBalanced)
                    <div class="text-xs">Saldo Akhir: Debit Rp {{ number_format($totalEndingD, 0, ',', '.') }} ≠ Kredit Rp {{ number_format($totalEndingC, 0, ',', '.') }} (Selisih: Rp {{ number_format(abs($totalEndingD - $totalEndingC), 0, ',', '.') }})</div>
                @endif
            </div>
        @endif

        {{-- Table --}}
        @if($groupedRows->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-12 text-center text-gray-400 text-sm">
                Tidak ada transaksi jurnal pada periode ini.
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th rowspan="2" class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200 w-20">Kode</th>
                            <th rowspan="2" class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-r border-gray-200">Nama Akun</th>
                            <th colspan="2" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200">
                                Saldo Awal<br>
                                <span class="font-normal normal-case text-gray-400">sebelum {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}</span>
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide border-r border-gray-200">
                                Mutasi Periode<br>
                                <span class="font-normal normal-case text-gray-400">{{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</span>
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wide">
                                Saldo Akhir<br>
                                <span class="font-normal normal-case text-gray-400">per {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</span>
                            </th>
                        </tr>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-100">Debit</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Kredit</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-100">Debit</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 border-r border-gray-200">Kredit</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 border-r border-gray-100">Debit</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedRows as $type => $group)
                            {{-- Account type section header --}}
                            <tr class="bg-gray-100 border-t border-gray-200">
                                <td colspan="8" class="px-3 py-1.5 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    {{ $typeLabels[$type] }}
                                </td>
                            </tr>

                            @foreach($group as $r)
                                <tr class="border-t border-gray-100 {{ $r->is_abnormal ? 'bg-amber-50' : 'hover:bg-gray-50' }}">
                                    <td class="px-3 py-2 font-mono text-xs text-gray-500 border-r border-gray-100">{{ $r->code }}</td>
                                    <td class="px-3 py-2 text-gray-800 border-r border-gray-200">
                                        {{ $r->name }}
                                        @if($r->is_abnormal)
                                            <span class="ml-1.5 inline-flex items-center gap-0.5 text-xs text-amber-700 font-medium">
                                                ⚠ saldo tidak normal
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Opening balance --}}
                                    <td class="px-3 py-2 text-right text-gray-600 border-r border-gray-100 tabular-nums">
                                        {{ $r->ob_d_col > 0 ? number_format($r->ob_d_col, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-600 border-r border-gray-200 tabular-nums">
                                        {{ $r->ob_c_col > 0 ? number_format($r->ob_c_col, 0, ',', '.') : '' }}
                                    </td>
                                    {{-- Period movement --}}
                                    <td class="px-3 py-2 text-right text-gray-700 border-r border-gray-100 tabular-nums">
                                        {{ $r->period_debit > 0 ? number_format($r->period_debit, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-gray-700 border-r border-gray-200 tabular-nums">
                                        {{ $r->period_credit > 0 ? number_format($r->period_credit, 0, ',', '.') : '' }}
                                    </td>
                                    {{-- Ending balance --}}
                                    <td class="px-3 py-2 text-right font-medium border-r border-gray-100 tabular-nums {{ $r->is_abnormal && $r->ending_d_col > 0 ? 'text-amber-700' : 'text-gray-800' }}">
                                        {{ $r->ending_d_col > 0 ? number_format($r->ending_d_col, 0, ',', '.') : '' }}
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium tabular-nums {{ $r->is_abnormal && $r->ending_c_col > 0 ? 'text-amber-700' : 'text-gray-800' }}">
                                        {{ $r->ending_c_col > 0 ? number_format($r->ending_c_col, 0, ',', '.') : '' }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Group subtotal --}}
                            <tr class="border-t border-gray-200 bg-gray-50 text-xs font-semibold text-gray-600">
                                <td colspan="2" class="px-3 py-2 border-r border-gray-200">
                                    Subtotal {{ $typeLabels[$type] }}
                                </td>
                                <td class="px-3 py-2 text-right border-r border-gray-100 tabular-nums">
                                    {{ $group->sum('ob_d_col') > 0 ? number_format($group->sum('ob_d_col'), 0, ',', '.') : '' }}
                                </td>
                                <td class="px-3 py-2 text-right border-r border-gray-200 tabular-nums">
                                    {{ $group->sum('ob_c_col') > 0 ? number_format($group->sum('ob_c_col'), 0, ',', '.') : '' }}
                                </td>
                                <td class="px-3 py-2 text-right border-r border-gray-100 tabular-nums">
                                    {{ $group->sum('period_debit') > 0 ? number_format($group->sum('period_debit'), 0, ',', '.') : '' }}
                                </td>
                                <td class="px-3 py-2 text-right border-r border-gray-200 tabular-nums">
                                    {{ $group->sum('period_credit') > 0 ? number_format($group->sum('period_credit'), 0, ',', '.') : '' }}
                                </td>
                                <td class="px-3 py-2 text-right border-r border-gray-100 tabular-nums">
                                    {{ $group->sum('ending_d_col') > 0 ? number_format($group->sum('ending_d_col'), 0, ',', '.') : '' }}
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    {{ $group->sum('ending_c_col') > 0 ? number_format($group->sum('ending_c_col'), 0, ',', '.') : '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 bg-gray-100 font-bold text-sm">
                            <td colspan="2" class="px-3 py-3 border-r border-gray-200 text-gray-700 uppercase tracking-wide text-xs">
                                TOTAL
                            </td>
                            {{-- Opening balance totals --}}
                            <td class="px-3 py-3 text-right border-r border-gray-100 tabular-nums {{ !$obBalanced ? 'text-red-700' : 'text-gray-700' }}">
                                Rp {{ number_format($totalObD, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right border-r border-gray-200 tabular-nums {{ !$obBalanced ? 'text-red-700' : 'text-gray-700' }}">
                                Rp {{ number_format($totalObC, 0, ',', '.') }}
                            </td>
                            {{-- Period movement totals --}}
                            <td class="px-3 py-3 text-right border-r border-gray-100 tabular-nums {{ !$periodBalanced ? 'text-red-700' : 'text-gray-800' }}">
                                Rp {{ number_format($totalPeriodD, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right border-r border-gray-200 tabular-nums {{ !$periodBalanced ? 'text-red-700' : 'text-gray-800' }}">
                                Rp {{ number_format($totalPeriodC, 0, ',', '.') }}
                            </td>
                            {{-- Ending balance totals --}}
                            <td class="px-3 py-3 text-right border-r border-gray-100 tabular-nums {{ !$endingBalanced ? 'text-red-700' : 'text-gray-900' }}">
                                Rp {{ number_format($totalEndingD, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right tabular-nums {{ !$endingBalanced ? 'text-red-700' : 'text-gray-900' }}">
                                Rp {{ number_format($totalEndingC, 0, ',', '.') }}
                            </td>
                        </tr>
                        {{-- Balance check row --}}
                        <tr class="bg-gray-50 text-xs">
                            <td colspan="2" class="px-3 py-1.5 text-gray-400 border-r border-gray-200">Seimbang?</td>
                            <td colspan="2" class="px-3 py-1.5 text-center border-r border-gray-200 {{ $obBalanced ? 'text-green-600' : 'text-red-600 font-semibold' }}">
                                {{ $obBalanced ? '✓ Seimbang' : '✗ Tidak Seimbang' }}
                            </td>
                            <td colspan="2" class="px-3 py-1.5 text-center border-r border-gray-200 {{ $periodBalanced ? 'text-green-600' : 'text-red-600 font-semibold' }}">
                                {{ $periodBalanced ? '✓ Seimbang' : '✗ Tidak Seimbang' }}
                            </td>
                            <td colspan="2" class="px-3 py-1.5 text-center {{ $endingBalanced ? 'text-green-600' : 'text-red-600 font-semibold' }}">
                                {{ $endingBalanced ? '✓ Seimbang' : '✗ Tidak Seimbang' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

    </div>
</x-admin-layout>
