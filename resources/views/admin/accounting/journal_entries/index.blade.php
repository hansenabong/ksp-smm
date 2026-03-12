<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Akuntansi</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jurnal</h2>
            </div>
            <div class="flex gap-2 print:hidden">
            <a href="{{ route('admin.accounting.journal_entries.export', array_filter(['date_from'=>$dateFrom,'date_to'=>$dateTo,'source_type'=>$sourceType,'show_voided'=>$showVoided?1:null])) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                ↓ Export Excel
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-700">Filter</span>
            </div>
            <div class="p-5">
                <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sumber</label>
                        <select name="source_type"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Semua</option>
                            @foreach($sourceTypes as $key => $label)
                                <option value="{{ $key }}" @selected($sourceType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="show_voided" value="1" @checked($showVoided)
                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600">
                            Tampilkan VOID
                        </label>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.accounting.journal_entries.index') }}"
                           class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Daftar Jurnal</span>
                <span class="text-xs text-gray-400">{{ $entries->total() }} entri</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#ID</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Debit</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Kredit</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dibuat oleh</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-gray-50 transition-colors {{ $entry->is_voided ? 'opacity-60' : '' }}">
                                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $entry->trx_date->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm font-mono text-gray-500">
                                    #{{ $entry->id }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-700 max-w-xs">
                                    {{ $entry->description }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @php $srcLabel = $sourceTypes[$entry->source_type] ?? $entry->source_type; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                        {{ $srcLabel }}
                                    </span>
                                    <span class="text-xs text-gray-400 ml-1">#{{ $entry->source_id }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right text-gray-700 whitespace-nowrap">
                                    Rp {{ number_format((int)$entry->total_debit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right text-gray-700 whitespace-nowrap">
                                    Rp {{ number_format((int)$entry->total_credit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($entry->is_voided)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-inset ring-red-200">
                                            VOID
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 ring-1 ring-inset ring-green-200">
                                            Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-sm text-gray-500">
                                    {{ $entry->creator?->name ?? $entry->creator?->email ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.accounting.journal_entries.show', $entry) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    Tidak ada entri jurnal pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($entries->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
