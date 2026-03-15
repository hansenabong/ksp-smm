<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Dokumen Legal</h2>
            <a href="{{ route('admin.legal.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Berkas
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Search --}}
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul berkas atau nama anggota..."
                   class="w-full max-w-sm border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <button class="px-4 py-2 bg-gray-100 text-gray-600 text-sm rounded-lg hover:bg-gray-200">Cari</button>
            @if(request('search'))
            <a href="{{ route('admin.legal.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Judul Berkas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Anggota</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-20">File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Dibuat Oleh</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-36">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($berkasList as $i => $berkas)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $berkasList->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.legal.show', $berkas) }}"
                               class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $berkas->title }}
                            </a>
                            @if($berkas->description)
                            <div class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ $berkas->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($berkas->member)
                                {{ $berkas->member->name }}
                                <div class="text-xs text-gray-400">{{ $berkas->member->member_no }}</div>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full">
                                {{ $berkas->documents_count ?? $berkas->documents->count() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $berkas->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $berkas->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.legal.show', $berkas) }}"
                               class="text-xs text-indigo-600 hover:underline">Lihat</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            Belum ada berkas legal.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $berkasList->links() }}
    </div>
</x-admin-layout>
