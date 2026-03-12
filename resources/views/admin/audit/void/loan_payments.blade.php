<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit VOID — Angsuran Pinjaman</h2>
            <div class="flex items-center gap-2 print:hidden">
                <a href="{{ route('admin.audit.void.loan_payments.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <style>@media print { .print\:hidden { display:none!important; } nav, aside, header .print\:hidden { display:none!important; } }</style>

    <div class="space-y-4">

        {{-- Filter --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 print:hidden">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Dari (VOID)</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                </div>
                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.audit.void.loan_payments') }}"
                       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-3 flex items-center gap-2">
            <span class="text-sm text-gray-500">Total VOID:</span>
            <span class="text-sm font-semibold text-gray-800">{{ number_format($payments->total()) }} pembayaran</span>
            @if(request()->hasAny(['date_from','date_to']))
                <span class="text-xs text-indigo-600 font-medium ml-2">(terfilter)</span>
            @endif
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">VOID At</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Tgl Trx</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Loan</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Anggota</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kas</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Alasan VOID</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($payments as $p)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($p->voided_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $p->trx_date }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <a href="{{ route('admin.loans.active.show', $p->loan_id) }}"
                                       class="text-indigo-600 hover:underline">#{{ $p->loan_id }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="font-medium">{{ $p->loan?->member?->member_no }}</span>
                                    <span class="text-gray-500">— {{ $p->loan?->member?->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $p->cashAccount?->name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                    Rp {{ number_format($p->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs">{{ $p->void_reason }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $p->voider?->email }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">
                                    Belum ada pembayaran angsuran yang di-VOID.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="print:hidden">{{ $payments->links() }}</div>

    </div>
</x-admin-layout>
