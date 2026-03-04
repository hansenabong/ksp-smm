<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit VOID - Kas</h2>
        <x-export-button route="admin.audit.void.cash.export" label="Export CSV" />
    </x-slot>
    
    <div class="space-y-4">

            <div class="bg-white p-4 rounded shadow">

                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div>
                        
                        <label class="text-sm">Dari (voided_at)</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Type</label>
                        <select name="type" class="w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            @foreach (['INCOME', 'EXPENSE', 'TRANSFER'] as $t)
                                <option value="{{ $t }}" @selected(request('type') == $t)>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
                        <a class="px-4 py-2 bg-gray-200 rounded" href="{{ route('admin.audit.void.cash') }}">Reset</a>
                        <x-export-button route="admin.audit.void.cash.export" label="Export CSV" />

                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">VOID At</th>
                            <th class="text-left p-3">Kode</th>
                            <th class="text-left p-3">Type</th>
                            <th class="text-left p-3">Kas</th>
                            <th class="text-right p-3">Jumlah</th>
                            <th class="text-left p-3">Alasan</th>
                            <th class="text-left p-3">Voided By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-t">
                                <td class="p-3">{{ $tx->voided_at }}</td>
                                <td class="p-3">{{ $tx->code }}</td>
                                <td class="p-3">{{ $tx->type }}</td>
                                <td class="p-3">
                                    @if ($tx->type === 'INCOME')
                                        {{ $tx->toCashAccount?->name }}
                                    @elseif($tx->type === 'EXPENSE')
                                        {{ $tx->fromCashAccount?->name }}
                                    @else
                                        {{ $tx->fromCashAccount?->name }} → {{ $tx->toCashAccount?->name }}
                                    @endif
                                </td>
                                <td class="p-3 text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                <td class="p-3">{{ $tx->void_reason }}</td>
                                <td class="p-3">{{ $tx->voider?->email }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3" colspan="7">Belum ada transaksi kas yang di-VOID.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $transactions->links() }}</div>
    </div>
</x-admin-layout>
