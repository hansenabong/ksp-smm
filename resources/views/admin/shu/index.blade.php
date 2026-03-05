<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">SHU — Sisa Hasil Usaha</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">

        {{-- Header actions --}}
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Daftar periode distribusi surplus koperasi</p>
            @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN','MANAGER']))
            <a href="{{ route('admin.shu.create') }}"
               class="inline-flex items-center gap-1.5 bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                + Periode Baru
            </a>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($periods->isEmpty())
            <div class="text-center py-16 text-gray-400 text-sm">Belum ada data SHU.</div>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Tahun</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-right">SHU Bruto</th>
                        <th class="px-4 py-3 text-center">Anggota</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($periods as $p)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-bold text-gray-800">{{ $p->year }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $p->date_from->format('d/m/Y') }} – {{ $p->date_to->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono">
                            Rp {{ number_format($p->shu_bruto, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $p->memberAllocations()->count() }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($p->isFinalized())
                            <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">FINALIZED</span>
                            @else
                            <span class="inline-block bg-yellow-100 text-yellow-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">DRAFT</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.shu.show', $p) }}" class="text-blue-600 hover:underline text-xs">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</x-admin-layout>
