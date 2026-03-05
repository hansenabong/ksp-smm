<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Saldo Kas</h2>
    </x-slot>

    <div class="space-y-5">

        {{-- Total Banner --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-5 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Saldo Semua Kas</div>
                <div class="text-3xl font-bold text-gray-800">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>
            <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
        </div>

        {{-- Per-Account Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($balances as $b)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="text-sm font-semibold text-gray-800">{{ $b->name }}</div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $b->type }}
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-800">
                        Rp {{ number_format((int)$b->balance, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Detail Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Rincian Saldo per Akun Kas</span>
                <span class="text-xs text-gray-400">{{ count($balances) }} akun</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama Akun Kas</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($balances as $b)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm font-medium text-gray-800">{{ $b->name }}</td>
                                <td class="px-5 py-3.5 text-sm text-gray-500">{{ $b->type }}</td>
                                <td class="px-5 py-3.5 text-sm text-right font-semibold text-gray-800">
                                    Rp {{ number_format((int)$b->balance, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50 border-t border-indigo-100">
                            <td colspan="2" class="px-5 py-3.5 text-sm font-semibold text-indigo-800">Total</td>
                            <td class="px-5 py-3.5 text-sm text-right font-bold text-indigo-800">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-admin-layout>
