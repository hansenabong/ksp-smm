<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Simpanan / Saldo Anggota</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $member->member_no }} — {{ $member->name }}
                </h2>
            </div>
            <div class="flex items-center gap-2 print:hidden">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition">
                    Cetak
                </button>
                <a href="{{ route('admin.savings.member_balance.index') }}"
                   class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg px-3 py-1.5 transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } }</style>

    {{-- Member info card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 mb-4 grid grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">No. Anggota</div>
            <div class="font-mono text-gray-800">{{ $member->member_no }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Nama</div>
            <div class="font-semibold text-gray-800">{{ $member->name }}</div>
        </div>
        @if($member->phone)
        <div>
            <div class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Telepon</div>
            <div class="text-gray-700">{{ $member->phone }}</div>
        </div>
        @endif
        <div>
            <div class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Total Saldo</div>
            <div class="font-bold text-gray-900 text-base">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Per-product passbook tables --}}
    @foreach($balances as $productId => $data)
        @if(empty($data['rows'])) @continue @endif
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <span class="font-semibold text-sm text-gray-800">{{ $data['product']->name }}</span>
                    <span class="ml-2 text-xs text-gray-400 font-mono">{{ $data['product']->code }}</span>
                </div>
                <div class="text-sm font-semibold {{ $data['balance'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                    Saldo: Rp {{ number_format($data['balance'], 0, ',', '.') }}
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-32">Tanggal</th>
                            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-36">Setoran</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-36">Penarikan</th>
                            <th class="text-right px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-36">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($data['rows'] as $row)
                            @php $tx = $row['tx']; @endphp
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-5 py-2 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-2 text-sm text-gray-700">
                                    {{ $tx->description ?: ($tx->type === 'DEPOSIT' ? 'Setoran' : 'Penarikan') }}
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-green-700">
                                    {{ $tx->type === 'DEPOSIT' ? 'Rp ' . number_format($tx->amount, 0, ',', '.') : '' }}
                                </td>
                                <td class="px-4 py-2 text-right text-sm text-red-600">
                                    {{ $tx->type === 'WITHDRAW' ? 'Rp ' . number_format($tx->amount, 0, ',', '.') : '' }}
                                </td>
                                <td class="px-5 py-2 text-right text-sm font-medium {{ $row['balance'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                                    Rp {{ number_format($row['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-200 font-semibold">
                            <td colspan="4" class="px-5 py-2.5 text-sm text-gray-700">Saldo Akhir</td>
                            <td class="px-5 py-2.5 text-right text-sm {{ $data['balance'] >= 0 ? 'text-gray-900' : 'text-red-700' }}">
                                Rp {{ number_format($data['balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach

    @if(collect($balances)->every(fn($d) => empty($d['rows'])))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-8 text-center text-sm text-gray-400">
            Anggota ini belum memiliki transaksi simpanan.
        </div>
    @endif

</x-admin-layout>
