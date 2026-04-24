<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pinjaman Approved</h2>
        </div>
    </x-slot>

    <div class="space-y-4">

        @if(session('success'))
            <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Aktif</div>
                <div class="mt-1 text-2xl font-bold text-gray-800">{{ $loans->total() }}</div>
                <div class="text-xs text-gray-400 mt-0.5">pinjaman berjalan</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pokok</div>
                <div class="mt-1 text-lg font-bold text-gray-800">Rp {{ number_format($totalPrincipal, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-0.5">tersalur</div>
            </div>
        </div>

        {{-- Search bar --}}
        <form method="GET" action="{{ route('admin.loans.active.index') }}"
              class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="flex-1 relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="{{ $search ?? '' }}"
                           placeholder="Cari nama anggota atau nomor anggota…"
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    Cari
                </button>
                @if(!empty($search))
                    <a href="{{ route('admin.loans.active.index') }}"
                       class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">
                    Daftar Pinjaman Approved
                    @if(!empty($search))
                        <span class="text-xs text-gray-400 font-normal">— pencarian: "{{ $search }}"</span>
                    @endif
                </span>
                <span class="text-xs text-gray-400">{{ $loans->total() }} data</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="text-left px-4 py-3">Pinjaman</th>
                            <th class="text-left px-4 py-3">Anggota</th>
                            <th class="text-right px-4 py-3">Pokok &amp; Angsuran</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-gray-50 transition-colors">

                                {{-- Pinjaman: ID + tanggal cair + produk --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">#{{ $loan->id }}</div>
                                    <div class="text-xs text-gray-400">{{ $loan->disbursed_date?->format('d/m/Y') ?? '-' }}</div>
                                    <div class="text-xs text-indigo-500 mt-0.5">{{ $loan->loanProduct?->name ?? '-' }}</div>
                                </td>

                                {{-- Anggota --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $loan->member?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">{{ $loan->member?->member_no ?? '-' }}</div>
                                </td>

                                {{-- Pokok + tenor + angsuran --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">Rp {{ number_format($loan->principal, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-400">{{ $loan->tenor_months }} bln</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}/bln
                                    </div>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.loans.active.show', $loan) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    @if(!empty($search))
                                        Tidak ada pinjaman yang cocok dengan pencarian "{{ $search }}".
                                    @else
                                        Belum ada pinjaman aktif.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($loans->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
