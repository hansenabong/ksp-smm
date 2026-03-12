<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Akuntansi / Jurnal</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Jurnal #{{ $entry->id }}</h2>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()"
                        class="print:hidden inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak PDF
                </button>
                <a href="{{ route('admin.accounting.journal_entries.index') }}"
                   class="print:hidden inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Entry Header Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($entry->is_voided)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold ring-1 ring-inset bg-red-50 text-red-700 ring-red-200">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span>
                            VOID
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold ring-1 ring-inset bg-green-50 text-green-700 ring-green-200">
                            <span class="w-2 h-2 rounded-full bg-green-400"></span>
                            Aktif
                        </span>
                    @endif
                    <span class="text-gray-400 text-sm">Jurnal #{{ $entry->id }}</span>
                </div>
                <div class="text-sm text-gray-500">{{ $entry->trx_date->format('d F Y') }}</div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Keterangan</div>
                    <div class="font-semibold text-gray-800">{{ $entry->description }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Sumber Transaksi</div>
                    <div class="font-semibold text-gray-800">
                        {{ $sourceTypes[$entry->source_type] ?? $entry->source_type }}
                        <span class="text-gray-400 font-normal ml-1">#{{ $entry->source_id }}</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Dibuat oleh</div>
                    <div class="font-semibold text-gray-800">{{ $entry->creator?->name ?? $entry->creator?->email ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 mb-0.5">Tanggal Dibuat</div>
                    <div class="font-semibold text-gray-800">{{ $entry->created_at?->format('d/m/Y H:i') }}</div>
                </div>
                @if($entry->is_voided)
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Di-VOID pada</div>
                        <div class="font-semibold text-red-700">{{ $entry->voided_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($entry->void_reason)
                        <div>
                            <div class="text-xs text-gray-400 mb-0.5">Alasan VOID</div>
                            <div class="font-semibold text-red-700">{{ $entry->void_reason }}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Journal Lines Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Baris Jurnal</span>
                <span class="text-xs text-gray-400">{{ $entry->lines->count() }} baris</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-8">No</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Akun</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">D/K</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Debit</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kredit</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan Baris</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($entry->lines as $i => $line)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-5 py-3.5 text-sm font-mono text-gray-600">
                                    {{ $line->account?->code ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-800">
                                    {{ $line->account?->name ?? '-' }}
                                    <div class="text-xs text-gray-400">{{ $line->account?->type }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ring-1 ring-inset',
                                        'bg-blue-50 text-blue-700 ring-blue-200' => $line->dc === 'D',
                                        'bg-orange-50 text-orange-700 ring-orange-200' => $line->dc === 'C',
                                    ])>
                                        {{ $line->dc === 'D' ? 'Debit' : 'Kredit' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right whitespace-nowrap {{ $line->dc === 'D' ? 'font-semibold text-gray-800' : 'text-gray-300' }}">
                                    @if($line->dc === 'D')
                                        Rp {{ number_format((int)$line->amount, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right whitespace-nowrap {{ $line->dc === 'C' ? 'font-semibold text-gray-800' : 'text-gray-300' }}">
                                    @if($line->dc === 'C')
                                        Rp {{ number_format((int)$line->amount, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $line->memo ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 {{ $totalDebit === $totalCredit ? 'bg-green-50' : 'bg-red-50' }}">
                            <td colspan="4" class="px-5 py-3.5 text-xs font-semibold {{ $totalDebit === $totalCredit ? 'text-green-800' : 'text-red-800' }} uppercase tracking-wide">
                                Total
                                @if($totalDebit !== $totalCredit)
                                    <span class="ml-2 text-red-600">(Tidak Seimbang!)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-bold {{ $totalDebit === $totalCredit ? 'text-green-800' : 'text-red-800' }} whitespace-nowrap">
                                Rp {{ number_format($totalDebit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-sm text-right font-bold {{ $totalDebit === $totalCredit ? 'text-green-800' : 'text-red-800' }} whitespace-nowrap">
                                Rp {{ number_format($totalCredit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
