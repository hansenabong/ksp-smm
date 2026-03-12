<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Akuntansi</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bagan Akun (COA)</h2>
            </div>
            <a href="{{ route('admin.accounting.accounts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                + Tambah Akun
            </a>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } }</style>

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 mb-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            <svg class="w-4 h-4 flex-shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 mb-4 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @php
        $typeBadge = [
            'ASSET'     => 'bg-blue-50 text-blue-700 ring-blue-200',
            'LIABILITY' => 'bg-orange-50 text-orange-700 ring-orange-200',
            'EQUITY'    => 'bg-purple-50 text-purple-700 ring-purple-200',
            'REVENUE'   => 'bg-green-50 text-green-700 ring-green-200',
            'EXPENSE'   => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $typeLabel = [
            'ASSET'     => 'Aset',
            'LIABILITY' => 'Liabilitas',
            'EQUITY'    => 'Ekuitas',
            'REVENUE'   => 'Pendapatan',
            'EXPENSE'   => 'Beban',
        ];
        $nbLabel = ['D' => 'Debit', 'C' => 'Kredit'];
        $nbBadge = ['D' => 'bg-sky-50 text-sky-700', 'C' => 'bg-amber-50 text-amber-700'];
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">{{ $accounts->count() }} akun terdaftar</span>
            <button onclick="window.print()" class="print:hidden text-xs text-gray-500 hover:text-gray-800 border border-gray-200 rounded px-2 py-1">
                Cetak PDF
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Kode</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Akun</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Tipe</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-24">Saldo Normal</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Parent</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-20">Status</th>
                        <th class="px-4 py-3 w-28 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($accounts as $acc)
                        @php
                            // Determine indent level by code length (rough heuristic)
                            $level = match(true) {
                                strlen($acc->code) <= 1 => 0,
                                strlen($acc->code) <= 2 => 0,
                                strlen($acc->code) <= 3 => 1,
                                strlen($acc->code) <= 4 => 2,
                                default                 => 3,
                            };
                            $indent = $level * 16;
                            $isParent = $acc->children->count() > 0;
                        @endphp
                        <tr class="hover:bg-gray-50/50 {{ !$acc->is_active ? 'opacity-50' : '' }}">
                            <td class="px-5 py-2.5 text-sm font-mono text-gray-700">{{ $acc->code }}</td>
                            <td class="px-5 py-2.5 text-sm text-gray-800" style="padding-left: {{ 20 + $indent }}px;">
                                @if($isParent)
                                    <span class="font-semibold">{{ $acc->name }}</span>
                                @else
                                    <span class="text-gray-400 mr-1">└</span>{{ $acc->name }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ring-1 ring-inset {{ $typeBadge[$acc->type] ?? '' }}">
                                    {{ $typeLabel[$acc->type] ?? $acc->type }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $nbBadge[$acc->normal_balance] ?? '' }}">
                                    {{ $nbLabel[$acc->normal_balance] ?? $acc->normal_balance }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500 font-mono">
                                {{ $acc->parent ? $acc->parent->code . ' — ' . \Illuminate\Support\Str::limit($acc->parent->name, 20) : '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($acc->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right print:hidden">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.accounting.accounts.edit', $acc) }}"
                                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('admin.accounting.accounts.toggle', $acc) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="text-xs {{ $acc->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-800' }} font-medium"
                                            onclick="return confirm('{{ $acc->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            {{ $acc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-400">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
