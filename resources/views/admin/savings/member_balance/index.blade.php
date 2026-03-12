<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Simpanan</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Saldo Simpanan Anggota</h2>
            </div>
            <button onclick="window.print()" class="print:hidden inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                Cetak PDF
            </button>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } }</style>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <span class="text-sm font-medium text-gray-700">{{ $members->count() }} anggota</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Anggota</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                        @foreach($products as $product)
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">
                                {{ $product->name }}
                            </th>
                        @endforeach
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                        <th class="px-4 py-3 print:hidden"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $grandTotal = 0; $productTotals = []; @endphp
                    @forelse($members as $member)
                        @php
                            $total = $memberTotals[$member->id] ?? 0;
                            $grandTotal += $total;
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-2.5 text-sm font-mono text-gray-600">{{ $member->member_no }}</td>
                            <td class="px-5 py-2.5 text-sm font-medium text-gray-800">{{ $member->name }}</td>
                            @foreach($products as $product)
                                @php
                                    $bal = $balanceMap[$member->id][$product->id] ?? 0;
                                    $productTotals[$product->id] = ($productTotals[$product->id] ?? 0) + $bal;
                                @endphp
                                <td class="px-4 py-2.5 text-right text-sm {{ $bal > 0 ? 'text-gray-800' : 'text-gray-300' }}">
                                    {{ $bal > 0 ? 'Rp ' . number_format($bal, 0, ',', '.') : '—' }}
                                </td>
                            @endforeach
                            <td class="px-5 py-2.5 text-right text-sm font-semibold {{ $total > 0 ? 'text-gray-900' : 'text-gray-300' }}">
                                {{ $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-right print:hidden">
                                <a href="{{ route('admin.savings.member_balance.show', $member) }}"
                                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 4 + $products->count() }}" class="px-5 py-8 text-center text-sm text-gray-400">Belum ada anggota.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200 font-semibold">
                        <td class="px-5 py-3 text-sm text-gray-700" colspan="2">Total</td>
                        @foreach($products as $product)
                            <td class="px-4 py-3 text-right text-sm text-gray-800">
                                Rp {{ number_format($productTotals[$product->id] ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="px-5 py-3 text-right text-sm text-gray-900">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                        <td class="print:hidden"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</x-admin-layout>
