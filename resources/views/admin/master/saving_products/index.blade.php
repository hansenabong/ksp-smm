<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Data - Jenis Simpanan</h2>
            <a href="{{ route('admin.master.saving_products.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">
                Tambah Jenis
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">

            @if(session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <div class="md:col-span-2">
                        <label class="text-sm">Search</label>
                        <input name="q" value="{{ request('q') }}"
                               class="w-full border rounded px-3 py-2"
                               placeholder="Nama jenis simpanan">
                    </div>

                    <div>
                        <label class="text-sm">Status</label>
                        <select name="status" class="w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            <option value="active" @selected(request('status') === 'active')>Aktif</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Filter</button>
                        <a class="px-4 py-2 bg-gray-200 rounded"
                           href="{{ route('admin.master.saving_products.index') }}">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Nama</th>
                            <th class="text-left p-3">Tipe</th>
                            <th class="text-left p-3">Bunga / Tenor</th>
                            <th class="text-left p-3">Withdrawable</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-right p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($savingProducts as $sp)
                            <tr class="border-t">
                                <td class="p-3 font-medium">{{ $sp->name }}</td>

                                <td class="p-3">
                                    @if($sp->is_deposito)
                                        <span class="px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800">DEPOSITO</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">REGULER</span>
                                    @endif
                                </td>

                                <td class="p-3 text-xs text-gray-600">
                                    @if($sp->is_deposito)
                                        {{ rtrim(rtrim(number_format((float)$sp->default_interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / {{ $sp->default_term_months }} bln
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    @if($sp->is_withdrawable)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">YES</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">NO</span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    @if($sp->is_active)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">AKTIF</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">NONAKTIF</span>
                                    @endif
                                </td>

                                <td class="p-3 text-right space-x-2">
                                    <a href="{{ route('admin.master.saving_products.edit', $sp) }}"
                                       class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Edit</a>

                                    <form class="inline" method="POST" action="{{ route('admin.master.saving_products.toggle', $sp) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 bg-gray-800 text-white rounded text-xs">
                                            {{ $sp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-3" colspan="6">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $savingProducts->links() }}</div>
    </div>
</x-admin-layout>