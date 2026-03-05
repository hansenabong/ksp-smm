<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Master Data</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kategori Kas</h2>
            </div>
            <a href="{{ route('admin.master.cash_categories.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                + Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">

        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @php
            $typeLabel = ['INCOME' => 'Pemasukan', 'EXPENSE' => 'Pengeluaran', 'TRANSFER' => 'Transfer'];
            $typeBadge = [
                'INCOME'   => 'bg-green-50 text-green-700 ring-green-200',
                'EXPENSE'  => 'bg-red-50 text-red-700 ring-red-200',
                'TRANSFER' => 'bg-blue-50 text-blue-700 ring-blue-200',
            ];
        @endphp

        @foreach(['INCOME', 'EXPENSE', 'TRANSFER'] as $type)
            @php $group = $categories[$type] ?? collect(); @endphp

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 ring-inset {{ $typeBadge[$type] }}">
                            {{ $typeLabel[$type] }}
                        </span>
                        <span class="text-sm font-medium text-gray-700">Kategori {{ $typeLabel[$type] }}</span>
                    </span>
                    <span class="text-xs text-gray-400">{{ $group->count() }} kategori</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Akun Ledger (Lawan)</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Urutan</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($group as $cat)
                                <tr class="hover:bg-gray-50 transition-colors {{ $cat->is_active ? '' : 'opacity-60' }}">
                                    <td class="px-5 py-3.5 text-sm font-medium text-gray-800">
                                        {{ $cat->name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-600">
                                        @if($cat->account)
                                            <span class="font-mono text-xs text-gray-500">{{ $cat->account->code }}</span>
                                            <span class="ml-1">{{ $cat->account->name }}</span>
                                        @elseif($type === 'TRANSFER')
                                            <span class="text-xs text-gray-400 italic">Otomatis (kas ↔ kas)</span>
                                        @else
                                            <span class="text-xs text-red-400 font-medium">Belum dipetakan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-gray-500">
                                        {{ $cat->description ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center text-sm text-gray-500">
                                        {{ $cat->sort_order }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($cat->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 ring-1 ring-inset ring-green-200">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 ring-1 ring-inset ring-gray-200">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('admin.master.cash_categories.edit', $cat) }}"
                                               class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-xs font-medium rounded-lg transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.master.cash_categories.toggle', $cat) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-xs font-medium rounded-lg transition
                                                        {{ $cat->is_active ? 'text-red-600' : 'text-green-700' }}">
                                                    {{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">
                                        Belum ada kategori {{ $typeLabel[$type] }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </div>
</x-admin-layout>
