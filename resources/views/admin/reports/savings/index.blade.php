<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan - Rekap Simpanan</h2>
            <a href="{{ route('admin.home') }}" class="px-4 py-2 bg-gray-200 rounded">Kembali</a>
        </div>
    </x-slot>

    <div class="space-y-4">

            <div class="bg-white p-4 rounded shadow">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="text-sm">Filter Anggota</label>
                        <select name="member_id" class="w-full border rounded px-3 py-2">
                            <option value="">Semua Anggota</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected((string)$memberId === (string)$m->id)>
                                    {{ $m->member_no }} - {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button class="px-4 py-2 bg-gray-800 text-white rounded">Tampilkan</button>
                        <a class="px-4 py-2 bg-gray-200 rounded" href="{{ route('admin.reports.savings') }}">Reset</a>
                        <x-export-button route="admin.reports.savings.export" />
                    </div>
                </form>
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <div class="p-4 border-b">
                    <div class="font-semibold">Saldo Simpanan (aktif, tidak termasuk VOID)</div>
                    <div class="text-xs text-gray-500">
                        Balance = Deposit - Withdraw untuk tiap anggota dan tiap jenis simpanan.
                    </div>
                </div>

                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Anggota</th>
                            @foreach($products as $p)
                                <th class="text-right p-3">{{ $p->name }}</th>
                            @endforeach
                            <th class="text-right p-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members->when($memberId, fn($c) => $c->where('id', (int)$memberId)) as $m)
                            @php($rowTotal = 0)
                            <tr class="border-t">
                                <td class="p-3 font-medium">{{ $m->member_no }} - {{ $m->name }}</td>

                                @foreach($products as $p)
                                    @php($cell = $matrix[$m->id][$p->id] ?? ['balance' => 0])
                                    @php($rowTotal += (int)$cell['balance'])
                                    <td class="p-3 text-right">
                                        Rp {{ number_format((int)$cell['balance'], 0, ',', '.') }}
                                    </td>
                                @endforeach

                                <td class="p-3 text-right font-semibold">
                                    Rp {{ number_format($rowTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3" colspan="{{ 2 + $products->count() }}">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Footer total per produk --}}
                    <tfoot class="bg-gray-50">
                        <tr class="border-t">
                            <th class="text-left p-3">Total per Jenis</th>
                            @php($grandTotal = 0)
                            @foreach($products as $p)
                                @php($colTotal = 0)
                                @foreach($members->when($memberId, fn($c) => $c->where('id', (int)$memberId)) as $m)
                                    @php($cell = $matrix[$m->id][$p->id] ?? ['balance' => 0])
                                    @php($colTotal += (int)$cell['balance'])
                                @endforeach
                                @php($grandTotal += $colTotal)
                                <th class="text-right p-3">Rp {{ number_format($colTotal, 0, ',', '.') }}</th>
                            @endforeach
                            <th class="text-right p-3">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

    </div>
</x-admin-layout>