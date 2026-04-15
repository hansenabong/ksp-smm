@php
    $returnUrl = request('return') ?? route($transaction->type === 'DEPOSIT' ? 'admin.savings.deposit.index' : 'admin.savings.withdraw.index');
    $badge = $transaction->type === 'DEPOSIT'
        ? 'bg-green-100 text-green-700'
        : 'bg-orange-100 text-orange-700';
@endphp
<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">VOID Transaksi Simpanan</h2>
            <a href="{{ $returnUrl }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <div class="space-y-4">

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4">
                    <div class="text-sm font-semibold text-red-700 mb-1">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-4 text-sm text-red-600 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Transaction detail --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <span class="text-sm font-semibold text-gray-700">Detail Transaksi Simpanan</span>
                    @if($transaction->is_voided)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">VOID</span>
                    @endif
                </div>
                <div class="px-5 py-4 space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tanggal</span>
                        <span class="text-gray-800">{{ $transaction->trx_date }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tipe</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ $transaction->type }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Anggota</span>
                        <span class="text-gray-800 font-medium">
                            {{ $transaction->member?->member_no }} — {{ $transaction->member?->name }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Jenis Simpanan</span>
                        <span class="text-gray-800">{{ $transaction->savingProduct?->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kas</span>
                        <span class="text-gray-800">{{ $transaction->cashAccount?->name }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2.5 mt-2">
                        <span class="text-gray-500 font-medium">Jumlah</span>
                        <span class="font-bold text-gray-800 text-base">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if(!$transaction->is_voided)
                {{-- VOID form --}}
                <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-red-100 bg-red-50">
                        <span class="text-sm font-semibold text-red-700">Konfirmasi VOID</span>
                        <p class="text-xs text-red-500 mt-0.5">Tindakan ini tidak dapat dibatalkan. Saldo simpanan anggota dan jurnal terkait akan ikut di-balik.</p>
                    </div>
                    <div class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.savings.void.store', $transaction) }}"
                              onsubmit="return confirm('Yakin ingin me-VOID transaksi simpanan ini?')">
                            @csrf
                            <input type="hidden" name="return" value="{{ $returnUrl }}">
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                    Alasan VOID <span class="text-red-500">*</span>
                                </label>
                                <textarea name="void_reason" rows="3" maxlength="255" required
                                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"
                                          placeholder="Jelaskan alasan pembatalan transaksi...">{{ old('void_reason') }}</textarea>
                                @error('void_reason')
                                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex gap-3">
                                <button type="submit"
                                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition">
                                    VOID Transaksi
                                </button>
                                <a href="{{ $returnUrl }}"
                                   class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
                    <div class="text-sm text-gray-500 mb-3">Transaksi ini sudah di-VOID.</div>
                    <div class="text-xs text-gray-400">Alasan: {{ $transaction->void_reason }}</div>
                    <a href="{{ $returnUrl }}"
                       class="mt-3 inline-flex px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Kembali
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-admin-layout>
