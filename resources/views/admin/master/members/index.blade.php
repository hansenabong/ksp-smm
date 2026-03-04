<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Data - Anggota</h2>
            <a href="{{ route('admin.master.members.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded">
                Tambah Anggota
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
                               placeholder="Member no / nama">
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
                           href="{{ route('admin.master.members.index') }}">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Member No</th>
                            <th class="text-left p-3">Nama</th>
                            <th class="text-left p-3">Phone</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-right p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $m)
                            <tr class="border-t">
                                <td class="p-3 font-medium">{{ $m->member_no }}</td>
                                <td class="p-3">{{ $m->name }}</td>
                                <td class="p-3">{{ $m->phone ?? '-' }}</td>
                                <td class="p-3">
                                    @if($m->is_active)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">AKTIF</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <a href="{{ route('admin.master.members.edit', $m) }}"
                                       class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Edit</a>

                                    <form class="inline" method="POST" action="{{ route('admin.master.members.toggle', $m) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-1 bg-gray-800 text-white rounded text-xs">
                                            {{ $m->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="p-3" colspan="5">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $members->links() }}</div>
    </div>
</x-admin-layout>