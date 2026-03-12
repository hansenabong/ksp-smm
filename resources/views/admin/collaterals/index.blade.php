<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Agunan — {{ $application->member->name ?? '-' }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pengajuan #{{ $application->id }} · {{ $application->member->member_no ?? '' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('create', \App\Models\LoanCollateral::class)
                <a href="{{ route('admin.loans.applications.collaterals.create', $application) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    + Tambah Agunan
                </a>
                @endcan
                <a href="{{ route('admin.loans.applications.show', $application) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">

        @if($collaterals->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-10 text-center">
                <p class="text-gray-400 text-sm">Belum ada data agunan untuk pengajuan ini.</p>
                <a href="{{ route('admin.loans.applications.collaterals.create', $application) }}"
                   class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    + Tambah Agunan
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                            <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                            <th class="px-4 py-3 text-right font-semibold">Nilai Taksiran</th>
                            <th class="px-4 py-3 text-center font-semibold">Dokumen</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Dilepas</th>
                            <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($collaterals as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                    {{ $c->type === 'SERTIPIKAT' ? 'bg-blue-100 text-blue-700' :
                                       ($c->type === 'BPKB' ? 'bg-purple-100 text-purple-700' :
                                       ($c->type === 'DEPOSITO' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $c->type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $c->description ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                Rp {{ number_format($c->estimated_value, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($c->document_path)
                                    <a href="{{ route('admin.loans.applications.collaterals.download', [$application, $c]) }}"
                                       class="text-indigo-600 hover:underline text-xs">Unduh</a>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($c->status === 'HELD')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Ditahan</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Dilepas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                @if($c->status === 'RELEASED')
                                    {{ $c->released_at?->format('d/m/Y') }}<br>
                                    <span class="text-gray-400">{{ $c->releasedBy?->name ?? '-' }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    @if($c->status === 'HELD')
                                        <a href="{{ route('admin.loans.applications.collaterals.release-form', [$application, $c]) }}"
                                           class="px-2 py-1 text-xs bg-green-100 hover:bg-green-200 text-green-700 rounded transition">Lepas</a>
                                    @endif
                                    @if($c->status === 'HELD')
                                    <form method="POST" action="{{ route('admin.loans.applications.collaterals.destroy', [$application, $c]) }}"
                                          onsubmit="return confirm('Hapus agunan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded transition">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary --}}
            <div class="bg-gray-50 rounded-xl border border-gray-200 px-5 py-3 flex items-center justify-between">
                <span class="text-sm text-gray-600">Total nilai taksiran agunan</span>
                <span class="text-sm font-bold text-gray-800">Rp {{ number_format($collaterals->sum('estimated_value'), 0, ',', '.') }}</span>
            </div>
        @endif

    </div>
</x-admin-layout>
