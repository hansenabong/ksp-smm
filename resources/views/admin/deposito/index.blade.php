<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Deposito</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.deposito.print.index', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print PDF
                </a>
                <a href="{{ route('admin.deposito.create') }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">
                    + Buka Deposito
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white p-4 rounded shadow">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label class="text-sm">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2">
                        <option value="">Semua</option>
                        <option value="ACTIVE"    @selected(request('status') === 'ACTIVE')>Aktif</option>
                        <option value="MATURED"   @selected(request('status') === 'MATURED')>Jatuh Tempo</option>
                        <option value="WITHDRAWN" @selected(request('status') === 'WITHDRAWN')>Sudah Cair</option>
                        <option value="CLOSED"    @selected(request('status') === 'CLOSED')>Closed</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm">Anggota</label>
                    <select name="member_id" class="w-full border rounded px-3 py-2">
                        <option value="">— Semua —</option>
                        @foreach($members as $m)
                            <option value="{{ $m->id }}" @selected(request('member_id') == $m->id)>
                                {{ $m->member_no ?? $m->id }} – {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm">Produk</label>
                    <select name="saving_product_id" class="w-full border rounded px-3 py-2">
                        <option value="">— Semua —</option>
                        @foreach($depositoProducts as $sp)
                            <option value="{{ $sp->id }}" @selected(request('saving_product_id') == $sp->id)>
                                {{ $sp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 bg-gray-800 text-white rounded text-sm">Filter</button>
                    <a class="px-4 py-2 bg-gray-200 rounded text-sm" href="{{ route('admin.deposito.index') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">Ref</th>
                        <th class="text-left p-3">Anggota</th>
                        <th class="text-left p-3">Produk</th>
                        <th class="text-right p-3">Pokok</th>
                        <th class="text-right p-3">Bunga / Tenor</th>
                        <th class="text-left p-3">Mulai</th>
                        <th class="text-left p-3">Jatuh Tempo</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-right p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depositos as $d)
                        @php
                            $disp = $d->displayStatus();
                            $statusClass = match(true) {
                                $disp === 'JATUH TEMPO' => 'bg-amber-100 text-amber-800',
                                $disp === 'ACTIVE'      => 'bg-emerald-100 text-emerald-800',
                                $disp === 'WITHDRAWN'   => 'bg-gray-200 text-gray-700',
                                default                 => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="border-t">
                            <td class="p-3 font-mono text-xs">{{ $d->reference_code }}</td>
                            <td class="p-3">
                                <div class="font-medium">{{ $d->depositorDisplayName() }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $d->member?->member_no ?? 'Non-Anggota' }}
                                </div>
                            </td>
                            <td class="p-3 text-xs">{{ $d->savingProduct->name }}</td>
                            <td class="p-3 text-right tabular-nums">Rp {{ number_format($d->principal_amount, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-xs text-gray-600">
                                {{ rtrim(rtrim(number_format((float)$d->interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / {{ $d->term_months }} bln
                            </td>
                            <td class="p-3 text-xs">{{ $d->start_date->format('d/m/Y') }}</td>
                            <td class="p-3 text-xs">{{ $d->maturity_date->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs rounded {{ $statusClass }}">{{ $disp }}</span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('admin.deposito.show', $d) }}"
                                   class="px-3 py-1 bg-indigo-600 text-white rounded text-xs">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="p-3 text-center text-gray-400" colspan="9">Belum ada kontrak deposito.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $depositos->links() }}</div>
    </div>
</x-admin-layout>
