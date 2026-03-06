<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                @php
                    $typeLabel = $tx->type === 'DEPOSIT' ? 'Setoran Simpanan' : 'Penarikan Simpanan';
                    $typeBg    = $tx->type === 'DEPOSIT'
                        ? 'bg-green-100 text-green-700 ring-green-200'
                        : 'bg-orange-100 text-orange-700 ring-orange-200';
                    $backRoute = $tx->type === 'DEPOSIT'
                        ? 'admin.savings.deposit.index'
                        : 'admin.savings.withdraw.index';
                    $voucherTitle = $tx->type === 'DEPOSIT'
                        ? 'Receipt Voucher (RV)'
                        : 'Payment Voucher (PV)';
                @endphp
                <div class="text-xs text-gray-400 mb-0.5">Simpanan / {{ $typeLabel }}</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $voucherTitle }} — #{{ $tx->id }}
                </h2>
            </div>
            <div class="flex gap-2">
                @if(!$tx->is_voided)
                <a href="{{ route('admin.savings.transactions.voucher', $tx) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Voucher
                </a>
                @endif
                <a href="{{ route($backRoute) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                    <a href="{{ route('admin.savings.transactions.voucher', $tx) }}" target="_blank"
                       class="ml-2 underline font-semibold">Cetak Voucher &rarr;</a>
                </div>
            @endif

            @if($tx->is_voided)
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm font-medium">
                    Transaksi ini telah di-VOID. Alasan: {{ $tx->void_reason }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">Detail Transaksi Simpanan</h3>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $typeBg }}">
                        {{ $typeLabel }}
                    </span>
                </div>
                <div class="p-5 grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">No. Transaksi</div>
                        <div class="font-mono font-semibold text-gray-800">#{{ $tx->id }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Ref # (Manual)</div>
                        <div class="font-mono font-semibold text-gray-800">{{ $tx->reference_code ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Anggota</div>
                        <div class="font-semibold text-gray-800">{{ $tx->member?->name ?? '—' }}</div>
                        <div class="text-xs text-gray-400">{{ $tx->member?->member_no }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Produk Simpanan</div>
                        <div class="font-semibold text-gray-800">{{ $tx->savingProduct?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal Transaksi</div>
                        <div class="font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($tx->trx_date)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal Entry</div>
                        <div class="font-semibold text-gray-800">
                            {{ $tx->created_at->translatedFormat('d M Y H:i') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kas</div>
                        <div class="font-semibold text-gray-800">{{ $tx->cashAccount?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Jumlah</div>
                        <div class="font-bold text-lg text-gray-800">Rp {{ number_format($tx->amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Keterangan</div>
                        <div class="font-semibold text-gray-800">{{ $tx->description ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Dibuat oleh</div>
                        <div class="font-semibold text-gray-800">{{ $tx->creator?->name ?? $tx->creator?->email ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</div>
                        @if($tx->is_voided)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-700">VOID</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-green-100 text-green-700">AKTIF</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>
