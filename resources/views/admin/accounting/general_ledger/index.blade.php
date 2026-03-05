<x-admin-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs text-gray-400 mb-0.5">Akuntansi</div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buku Besar</h2>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-700">Pilih Akun &amp; Periode</span>
            </div>
            <div class="p-5">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Akun</label>
                        <select name="account_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">— Pilih Akun —</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected($accountId == $acc->id)>
                                    {{ $acc->code }} — {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Tampilkan
                        </button>
                        <a href="{{ route('admin.accounting.general_ledger.index') }}"
                           class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if(!$selectedAccount)
            {{-- Placeholder --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-16 text-center">
                <div class="text-gray-300 mb-3">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-gray-400 text-sm">Pilih akun dan periode untuk menampilkan buku besar.</p>
            </div>
        @else
            {{-- Account Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Akun yang ditampilkan</div>
                    <div class="text-lg font-bold text-gray-800">{{ $selectedAccount->code }} — {{ $selectedAccount->name }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-xs text-gray-400">Tipe</div>
                        <div class="text-sm font-semibold text-gray-700">{{ $selectedAccount->type }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400">Normal Saldo</div>
                        <div class="text-sm font-semibold text-gray-700">{{ $selectedAccount->normal_balance === 'D' ? 'Debit' : 'Kredit' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-400">Periode</div>
                        <div class="text-sm font-semibold text-gray-700">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Ledger Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Rincian Mutasi</span>
                    <span class="text-xs text-gray-400">{{ $lines->count() }} transaksi dalam periode</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Debit</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kredit</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            {{-- Opening balance row --}}
                            <tr class="bg-indigo-50">
                                <td class="px-5 py-3 text-xs font-semibold text-indigo-700">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</td>
                                <td colspan="4" class="px-5 py-3 text-xs font-semibold text-indigo-700 italic">Saldo Awal Periode</td>
                                <td class="px-5 py-3 text-xs font-bold text-indigo-800 text-right">
                                    Rp {{ number_format($openingBalance, 0, ',', '.') }}
                                </td>
                            </tr>

                            @forelse($lines as $line)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($line->trx_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-700">
                                        {{ $line->entry_description }}
                                        @if($line->memo)
                                            <div class="text-xs text-gray-400">{{ $line->memo }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('admin.accounting.journal_entries.show', $line->journal_entry_id) }}"
                                           class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-mono">
                                            #{{ $line->journal_entry_id }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-right text-gray-700 whitespace-nowrap">
                                        @if($line->debit_amount > 0)
                                            Rp {{ number_format($line->debit_amount, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-right text-gray-700 whitespace-nowrap">
                                        @if($line->credit_amount > 0)
                                            Rp {{ number_format($line->credit_amount, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-right font-semibold whitespace-nowrap {{ $line->running_balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                                        Rp {{ number_format($line->running_balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                                        Tidak ada mutasi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            {{-- Period totals --}}
                            <tr class="bg-gray-50 border-t border-gray-200">
                                <td colspan="3" class="px-5 py-3.5 text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                    Total Mutasi Periode
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right font-bold text-gray-800 whitespace-nowrap">
                                    Rp {{ number_format($periodDebit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-sm text-right font-bold text-gray-800 whitespace-nowrap">
                                    Rp {{ number_format($periodCredit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5"></td>
                            </tr>
                            {{-- Closing balance --}}
                            <tr class="bg-indigo-50 border-t border-indigo-100">
                                <td class="px-5 py-3.5 text-xs font-semibold text-indigo-700">{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td>
                                <td colspan="4" class="px-5 py-3.5 text-xs font-semibold text-indigo-700 italic">Saldo Akhir Periode</td>
                                <td class="px-5 py-3.5 text-sm text-right font-bold text-indigo-800 whitespace-nowrap">
                                    Rp {{ number_format($closingBalance, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-admin-layout>
