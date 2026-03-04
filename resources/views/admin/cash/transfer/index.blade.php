<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kas - Transfer</h2>
            <a href="{{ route('admin.cash.transfer.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">
                Tambah Transfer
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">

            @if (session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div>
                        <label class="text-sm">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Dari Kas</label>
                        <select name="from_cash_account_id" class="w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            @foreach ($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(request('from_cash_account_id') == $acc->id)>
                                    {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Ke Kas</label>
                        <select name="to_cash_account_id" class="w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            @foreach ($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(request('to_cash_account_id') == $acc->id)>
                                    {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Kategori</label>
                        <select name="category" class="w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" @selected(request('category') == $cat)>{{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
                        <a class="px-4 py-2 bg-gray-200 rounded"
                            href="{{ route('admin.cash.transfer.index') }}">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Kode</th>
                            <th class="text-left p-3">Tanggal</th>
                            <th class="text-left p-3">Dari</th>
                            <th class="text-left p-3">Ke</th>
                            <th class="text-left p-3">Kategori</th>
                            <th class="text-left p-3">Uraian</th>
                            <th class="text-right p-3">Jumlah</th>
                            <th class="text-left p-3">User</th>
                            <th class="text-left p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="border-t">
                                <td class="p-3">{{ $tx->code }}</td>
                                <td class="p-3">{{ $tx->trx_date }}</td>
                                <td class="p-3">{{ $tx->fromCashAccount?->name }}</td>
                                <td class="p-3">{{ $tx->toCashAccount?->name }}</td>
                                <td class="p-3">{{ $tx->category }}</td>
                                <td class="p-3">{{ $tx->description }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                                <td class="p-3">{{ $tx->creator?->email }}</td>
                                <td class="p-3">
                                    @if ($tx->is_voided)
                                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">VOID</span>
                                    @else
                                        <a class="px-3 py-1 bg-red-600 text-white rounded text-xs"
                                            href="{{ route('admin.cash.void.create', ['tx' => $tx->id, 'return' => url()->full()]) }}">
                                            VOID
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3" colspan="9">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $transactions->links() }}
            </div>
    </div>
</x-admin-layout>
