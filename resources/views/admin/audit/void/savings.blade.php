<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit VOID - Simpanan</h2>
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
                            @foreach(['DEPOSIT','WITHDRAW'] as $t)
                                <option value="{{ $t }}" @selected(request('type') == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
                        <a class="px-4 py-2 bg-gray-200 rounded" href="{{ route('admin.audit.void.savings') }}">Reset</a>
                        <x-export-button route="admin.audit.void.savings.export" label="Export CSV" />
                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">VOID At</th>
                            <th class="text-left p-3">Tanggal Trx</th>
                            <th class="text-left p-3">Anggota</th>
                            <th class="text-left p-3">Jenis</th>
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
                                <td class="p-3">{{ $tx->trx_date }}</td>
                                <td class="p-3">{{ $tx->member?->member_no ?? $tx->member_id }} - {{ $tx->member?->name }}</td>
                                <td class="p-3">{{ $tx->type }} / {{ $tx->savingProduct?->name }}</td>
                                <td class="p-3">{{ $tx->cashAccount?->name }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                <td class="p-3">{{ $tx->void_reason }}</td>
                                <td class="p-3">{{ $tx->voider?->email }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-3" colspan="8">Belum ada transaksi simpanan yang di-VOID.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $transactions->links() }}</div>
    </div>
</x-admin-layout>