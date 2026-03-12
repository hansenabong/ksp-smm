<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Data — Kas / Bank</h2>
            <a href="{{ route('admin.master.cash_accounts.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Tambah Kas
            </a>
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

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Cari</label>
                    <input name="q" value="{{ request('q') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="Nama kas" style="min-width:180px">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipe</label>
                    <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">Semua</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(request('type') == $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</label>
                    <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">Semua</option>
                        <option value="active"   @selected(request('status') === 'active')>Aktif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.master.cash_accounts.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Daftar Kas / Bank</span>
                <span class="text-xs text-gray-400">{{ $cashAccounts->total() }} data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="text-left px-5 py-3">Nama</th>
                            <th class="text-left px-5 py-3">Tipe</th>
                            <th class="text-left px-5 py-3">Akun Ledger</th>
                            <th class="text-center px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($cashAccounts as $acc)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-gray-800">{{ $acc->name }}</td>
                                <td class="px-5 py-3.5">
                                    @php
                                        $typeBadge = match($acc->type) {
                                            'BANK'  => 'bg-blue-50 text-blue-700',
                                            'CHECK' => 'bg-purple-50 text-purple-700',
                                            'GIRO'  => 'bg-teal-50 text-teal-700',
                                            default => 'bg-amber-50 text-amber-700',
                                        };
                                        $typeLabel = $typeLabels[$acc->type] ?? $acc->type;
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeBadge }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($acc->ledgerAccount)
                                        <div class="text-sm text-gray-700">
                                            <span class="font-mono text-xs text-gray-500">{{ $acc->ledgerAccount->code }}</span>
                                            {{ $acc->ledgerAccount->name }}
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-red-600 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Belum dipetakan — klik Edit
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($acc->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.master.cash_accounts.edit', $acc) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-medium rounded-lg transition">
                                        Edit
                                    </a>
                                    <form class="inline ml-1" method="POST"
                                          action="{{ route('admin.master.cash_accounts.toggle', $acc) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition
                                                {{ $acc->is_active
                                                    ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200'
                                                    : 'bg-green-50 hover:bg-green-100 text-green-700 border border-green-200' }}">
                                            {{ $acc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    Belum ada data kas / bank.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cashAccounts->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $cashAccounts->links() }}
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
