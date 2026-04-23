<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pemasukan Kas</h2>
            <a href="{{ route('admin.cash.income.index') }}"
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
                <div class="text-sm font-semibold text-gray-700">Pemasukan Kas</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST" action="{{ route('admin.cash.income.store') }}" class="px-5 py-5 space-y-4"
                  x-data="{
                      accountTypeMap: @js($accountTypeMap),
                      selectedAccountId: '{{ old('to_cash_account_id', '') }}',
                      get accountType() { return this.accountTypeMap[this.selectedAccountId] ?? '' },
                      get isCheckOrGiro() { return this.accountType === 'CHECK' || this.accountType === 'GIRO' },
                      get refLabel() {
                          if (this.accountType === 'CHECK') return 'No. Cek';
                          if (this.accountType === 'GIRO')  return 'No. Bilyet Giro';
                          return 'No. Referensi';
                      },
                      get refPlaceholder() {
                          if (this.accountType === 'CHECK') return 'Contoh: CEK-001234';
                          if (this.accountType === 'GIRO')  return 'Contoh: BG-001234';
                          return 'Contoh: 24487D';
                      }
                  }">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                    @error('trx_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kas Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="to_cash_account_id"
                            x-model="selectedAccountId"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        <option value="">— Pilih Kas —</option>
                        @foreach ($cashAccounts as $acc)
                            <option value="{{ $acc->id }}" @selected(old('to_cash_account_id') == $acc->id)>
                                {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_cash_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="cash_category_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        <option value="">— Pilih Kategori —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('cash_category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('cash_category_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Jenis Transaksi <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="payment_method"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Pilih —</option>
                        <option value="TUNAI"    @selected(old('payment_method') === 'TUNAI')>Tunai</option>
                        <option value="TRANSFER" @selected(old('payment_method') === 'TRANSFER')>Transfer</option>
                        <option value="CEK"      @selected(old('payment_method') === 'CEK')>Cek</option>
                        <option value="GIRO"     @selected(old('payment_method') === 'GIRO')>Giro</option>
                    </select>
                    @error('payment_method') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" data-money name="amount" value="{{ old('amount') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="0" required>
                    @error('amount') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Uraian <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           maxlength="255" placeholder="Keterangan tambahan">
                    @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kode Referensi <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="ref_number" value="{{ old('ref_number') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300"
                           maxlength="150" placeholder="No. bukti, kode transfer, dsb.">
                    @error('ref_number') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- No. Cek / Bilyet — hanya tampil saat tipe CHECK atau GIRO --}}
                <div x-show="isCheckOrGiro" x-transition>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        <span x-text="refLabel">No. Cek / Bilyet</span> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="reference_code" value="{{ old('reference_code') }}"
                           :required="isCheckOrGiro"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-purple-300 border-purple-200 transition"
                           maxlength="100"
                           :placeholder="refPlaceholder">
                    <p class="text-xs text-gray-400 mt-1">
                        <span x-show="accountType === 'CHECK'">Nomor seri cek yang diterima.</span>
                        <span x-show="accountType === 'GIRO'">Nomor bilyet giro yang diterima.</span>
                    </p>
                    @error('reference_code') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Simpan
                    </button>
                    <a href="{{ route('admin.cash.income.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
