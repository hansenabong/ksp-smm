<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengajuan Pinjaman</h2>
            @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN_DATA']))
            <a href="{{ route('admin.loans.applications.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Tambah Pengajuan
            </a>
            @endif
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

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Semua Pengajuan</span>
                    <span class="text-xs text-gray-400">{{ $applications->total() }} data</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Produk</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nominal</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tenor</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dibuat oleh</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($applications as $app)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                        {{ $app->trx_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="text-sm font-medium text-gray-800">{{ $app->member?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-400">{{ $app->member?->member_no ?? $app->member_id }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                                        {{ $app->loanProduct?->name ?? '-' }}
                                        @if($app->method)
                                            <span class="text-xs text-gray-400">({{ $app->method }})</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-right font-semibold text-gray-800 whitespace-nowrap">
                                        Rp {{ number_format($app->principal, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-center text-gray-600">
                                        {{ $app->tenor_months }} bln
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @php
                                            [$bg, $dot] = match($app->status) {
                                                'PENDING'           => ['bg-yellow-50 text-yellow-700 ring-yellow-200', 'bg-yellow-400'],
                                                'FINANCE_REVIEWED'  => ['bg-blue-50 text-blue-700 ring-blue-200', 'bg-blue-400'],
                                                'APPROVED'          => ['bg-green-50 text-green-700 ring-green-200', 'bg-green-400'],
                                                'REJECTED'          => ['bg-red-50 text-red-700 ring-red-200', 'bg-red-400'],
                                                default             => ['bg-gray-50 text-gray-600 ring-gray-200', 'bg-gray-400'],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 ring-inset {{ $bg }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                                            {{ $app->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">
                                        {{ $app->creator?->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($app->status === 'PENDING' &&
                                                (auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN','MANAGER']) ||
                                                 (auth()->user()->hasRole('PETUGAS') && (int)$app->created_by === auth()->id())))
                                                <a href="{{ route('admin.loans.applications.edit', $app) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition">
                                                    Edit
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.loans.applications.show', $app) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                                Detail &rarr;
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-gray-400 text-sm">
                                        Belum ada data pengajuan pinjaman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($applications->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>

    </div>
</x-admin-layout>
