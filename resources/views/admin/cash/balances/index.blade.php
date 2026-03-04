<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Saldo Kas (Real-time)</h2>
    </x-slot>

    <div class="space-y-4">

            <div class="bg-white p-4 rounded shadow">
                <div class="text-sm text-gray-500">Total Saldo Semua Kas</div>
                <div class="text-2xl font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($balances as $b)
                    <div class="bg-white p-4 rounded shadow">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold">{{ $b->name }}</div>
                            <span class="text-xs px-2 py-1 rounded bg-gray-100">{{ $b->type }}</span>
                        </div>
                        <div class="mt-2 text-xl font-semibold">
                            Rp {{ number_format((int)$b->balance, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded shadow overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Kas</th>
                            <th class="text-left p-3">Type</th>
                            <th class="text-right p-3">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balances as $b)
                            <tr class="border-t">
                                <td class="p-3">{{ $b->name }}</td>
                                <td class="p-3">{{ $b->type }}</td>
                                <td class="p-3 text-right">Rp {{ number_format((int)$b->balance, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

    </div>
</x-admin-layout>