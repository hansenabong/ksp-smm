<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Jenis Simpanan</h2>
            <a href="{{ route('admin.master.saving_products.index') }}"
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
                <div class="text-sm font-semibold text-gray-700">Jenis Simpanan</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST" action="{{ route('admin.master.saving_products.update', $savingProduct) }}"
                  class="px-5 py-5 space-y-4"
                  x-data="{ isDeposito: {{ old('is_deposito', $savingProduct->is_deposito) ? 'true' : 'false' }} }">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name', $savingProduct->name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: Simpanan Pokok" required>
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Akun Buku Besar (Kewajiban) <span class="text-xs text-gray-400 font-normal">(opsional — wajib untuk jurnal otomatis)</span>
                    </label>
                    <select name="ledger_account_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Pilih Akun Kewajiban —</option>
                        @foreach($liabilityAccounts as $acc)
                            <option value="{{ $acc->id }}" @selected(old('ledger_account_id', $savingProduct->ledger_account_id) == $acc->id)>
                                {{ $acc->code }} – {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Pilih akun LIABILITY (2xxx) yang mencatat simpanan anggota ini di Buku Besar.</p>
                    @error('ledger_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <input type="hidden" name="is_deposito" value="0">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_deposito" name="is_deposito" value="1"
                           x-model="isDeposito"
                           class="rounded border-gray-300">
                    <label for="is_deposito" class="text-sm text-gray-700">Deposito (jangka waktu &amp; bunga tetap)</label>
                </div>

                <div x-show="isDeposito" x-cloak class="space-y-4 p-4 bg-indigo-50/50 border border-indigo-100 rounded-lg">
                    <div class="text-xs text-indigo-700 font-semibold uppercase tracking-wide">Konfigurasi Deposito</div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Bunga / tahun (%) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" max="100"
                                   name="default_interest_rate_pct"
                                   value="{{ old('default_interest_rate_pct', $savingProduct->default_interest_rate_pct) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: 6.50">
                            @error('default_interest_rate_pct') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Jangka waktu (bulan) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" min="1" max="240"
                                   name="default_term_months"
                                   value="{{ old('default_term_months', $savingProduct->default_term_months) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: 12">
                            @error('default_term_months') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-500">
                        Tanggal mulai &amp; tanggal jatuh tempo dicatat per kontrak saat anggota membuka deposito (bukan di master).
                        Penarikan otomatis dikunci sampai tanggal jatuh tempo.
                    </p>
                </div>

                <input type="hidden" name="is_withdrawable" value="0">
                <div class="flex items-center gap-2" x-show="!isDeposito" x-cloak>
                    <input type="checkbox" id="is_withdrawable" name="is_withdrawable" value="1"
                           @checked(old('is_withdrawable', $savingProduct->is_withdrawable))
                           class="rounded border-gray-300">
                    <label for="is_withdrawable" class="text-sm text-gray-700">Bisa ditarik (withdraw)</label>
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $savingProduct->is_active))
                           class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Update
                    </button>
                    <a href="{{ route('admin.master.saving_products.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
