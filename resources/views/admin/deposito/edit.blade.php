<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Deposito {{ $deposito->reference_code }}
            </h2>
            <a href="{{ route('admin.deposito.show', $deposito) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        @if($errors->any())
            <div class="mb-4 flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="list-disc ml-3 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Rekening Bank Pencairan</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    Hanya data rekening bank yang dapat diubah. Pokok, bunga, tenor, dan tanggal tidak dapat diubah karena sudah terkait transaksi.
                </div>
            </div>

            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 text-xs text-gray-600 space-y-1">
                <div><span class="text-gray-400">Anggota:</span> <span class="font-semibold text-gray-800">{{ $deposito->member->name }}</span></div>
                <div><span class="text-gray-400">Produk:</span> {{ $deposito->savingProduct->name }}</div>
                <div><span class="text-gray-400">Pokok:</span> Rp {{ number_format($deposito->principal_amount, 0, ',', '.') }}</div>
                <div><span class="text-gray-400">Tenor:</span> {{ $deposito->term_months }} bln @ {{ rtrim(rtrim(number_format((float)$deposito->interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / tahun</div>
            </div>

            <form method="POST" action="{{ route('admin.deposito.update', $deposito) }}" class="px-5 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Pemilik Rekening
                    </label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $deposito->bank_account_name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           maxlength="100" placeholder="Nama sesuai rekening">
                    @error('bank_account_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nomor Rekening
                    </label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $deposito->bank_account_no) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300"
                           maxlength="50" placeholder="cth: 1234567890">
                    @error('bank_account_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Bank
                    </label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $deposito->bank_name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           maxlength="100" placeholder="cth: BCA, Mandiri, BRI">
                    @error('bank_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Update
                    </button>
                    <a href="{{ route('admin.deposito.show', $deposito) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
