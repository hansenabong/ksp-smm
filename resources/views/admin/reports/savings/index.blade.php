<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan — Rekap Simpanan</h2>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-700">Filter</span>
            </div>
            <div class="p-5">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Filter Anggota</label>
                        <select name="member_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Semua Anggota</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected((string)$memberId === (string)$m->id)>
                                    {{ $m->member_no }} - {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                            Tampilkan
                        </button>
                        <a href="{{ route('admin.reports.savings') }}"
                           class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                            Reset
                        </a>
                        <x-export-button route="admin.reports.savings.export" />
                    </div>
                </form>
            </div>
        </div>

        {{-- Savings Balance Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100">
                <div class="text-sm font-medium text-gray-700">Saldo Simpanan per Anggota</div>
                <p class="text-xs text-gray-400 mt-0.5">Saldo = Setoran &minus; Penarikan (transaksi aktif, tidak termasuk VOID).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota</th>
                            @foreach($products as $p)
                                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $p->name }}</th>
                            @endforeach
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($members->when($memberId, fn($c) => $c->where('id', (int)$memberId)) as $m)
                            @php($rowTotal = 0)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="text-sm font-medium text-gray-800">{{ $m->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $m->member_no }}</div>
                                </td>
                                @foreach($products as $p)
                                    @php($cell = $matrix[$m->id][$p->id] ?? ['balance' => 0])
                                    @php($rowTotal += (int)$cell['balance'])
                                    <td class="px-5 py-3.5 text-sm text-right text-gray-700">
                                        Rp {{ number_format((int)$cell['balance'], 0, ',', '.') }}
                                    </td>
                                @endforeach
                                <td class="px-5 py-3.5 text-sm text-right font-semibold text-gray-800">
                                    Rp {{ number_format($rowTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + $products->count() }}" class="px-5 py-12 text-center text-gray-400 text-sm">
                                    Belum ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50 border-t border-indigo-100">
                            <th class="text-left px-5 py-3.5 text-xs font-semibold text-indigo-800 uppercase tracking-wide">Total per Jenis</th>
                            @php($grandTotal = 0)
                            @foreach($products as $p)
                                @php($colTotal = 0)
                                @foreach($members->when($memberId, fn($c) => $c->where('id', (int)$memberId)) as $m)
                                    @php($cell = $matrix[$m->id][$p->id] ?? ['balance' => 0])
                                    @php($colTotal += (int)$cell['balance'])
                                @endforeach
                                @php($grandTotal += $colTotal)
                                <th class="text-right px-5 py-3.5 text-sm font-bold text-indigo-800">
                                    Rp {{ number_format($colTotal, 0, ',', '.') }}
                                </th>
                            @endforeach
                            <th class="text-right px-5 py-3.5 text-sm font-bold text-indigo-800">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
