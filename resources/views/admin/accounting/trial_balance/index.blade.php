<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Accounting - Trial Balance (Neraca Saldo)</h2>
        </div>
    </x-slot>

    <div class="space-y-4">

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <div>
                        <label class="text-sm">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                               class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="show_zero" value="1" @checked($showZero)>
                            <span class="text-sm">Tampilkan akun nol</span>
                        </label>
                    </div>

                    <div class="flex items-end gap-2 md:col-span-3">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
                        <a class="px-4 py-2 bg-gray-200 rounded" href="{{ route('admin.accounting.trial_balance.index') }}">Reset</a>
                    </div>
                </form>
            </div>

            @if($diff !== 0)
                <div class="p-3 bg-red-100 text-red-800 rounded">
                    Trial Balance tidak balance. Selisih (Debit - Kredit):
                    <b>Rp {{ number_format($diff, 0, ',', '.') }}</b>
                </div>
            @else
                <div class="p-3 bg-green-100 text-green-800 rounded">
                    Trial Balance balance. Total Debit = Total Kredit.
                </div>
            @endif

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Kode</th>
                            <th class="text-left p-3">Nama Akun</th>
                            <th class="text-left p-3">Tipe</th>
                            <th class="text-right p-3">Debit</th>
                            <th class="text-right p-3">Kredit</th>
                            <th class="text-right p-3">Saldo (Normal)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr class="border-t">
                                <td class="p-3">{{ $r->code }}</td>
                                <td class="p-3">{{ $r->name }}</td>
                                <td class="p-3">{{ $r->type }}</td>
                                <td class="p-3 text-right">Rp {{ number_format((int)$r->debit, 0, ',', '.') }}</td>
                                <td class="p-3 text-right">Rp {{ number_format((int)$r->credit, 0, ',', '.') }}</td>
                                <td class="p-3 text-right">
                                    Rp {{ number_format((int)$r->balance, 0, ',', '.') }}
                                    <span class="text-xs text-gray-500">({{ $r->normal_balance }})</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3" colspan="6">Belum ada data journal pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t bg-gray-50 font-semibold">
                            <td class="p-3" colspan="3">TOTAL</td>
                            <td class="p-3 text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                            <td class="p-3 text-right">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                            <td class="p-3 text-right">
                                Selisih: Rp {{ number_format($diff, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

    </div>
</x-admin-layout>