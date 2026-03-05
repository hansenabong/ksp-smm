<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Jenis Pinjaman</h2>
            <a href="{{ route('admin.master.loan_products.index') }}"
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
                <div class="text-sm font-semibold text-gray-700">Jenis Pinjaman</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST" action="{{ route('admin.master.loan_products.update', $loanProduct) }}" class="px-5 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name', $loanProduct->name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: Pinjaman Flat" required>
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Metode <span class="text-red-500">*</span>
                    </label>
                    <select name="method"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        @foreach($methods as $m)
                            <option value="{{ $m }}" @selected(old('method', $loanProduct->method) === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                    @error('method') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Bunga per Bulan (Basis Points) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="interest_rate_monthly_bp"
                           value="{{ old('interest_rate_monthly_bp', $loanProduct->interest_rate_monthly_bp) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           min="0" required>
                    <div class="text-xs text-gray-400 mt-1">100 bp = 1.00% / bulan &middot; cth: 200 = 2.00%/bln</div>
                    @error('interest_rate_monthly_bp') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Biaya Admin (Rp) <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="number" name="admin_fee"
                           value="{{ old('admin_fee', $loanProduct->admin_fee) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           min="0">
                    @error('admin_fee') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Maks Tenor (bulan) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="max_tenor_months"
                           value="{{ old('max_tenor_months', $loanProduct->max_tenor_months) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           min="1" max="360" required>
                    @error('max_tenor_months') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tarif Denda</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Denda Keterlambatan <span class="text-xs text-gray-400 font-normal">(bp/hari)</span>
                            </label>
                            <input type="number" name="penalty_daily_rate_bp"
                                   value="{{ old('penalty_daily_rate_bp', $loanProduct->penalty_daily_rate_bp ?? 30) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   min="0" max="10000">
                            <div class="text-xs text-gray-400 mt-1">Default: 30 bp = 0.30%/hari</div>
                            @error('penalty_daily_rate_bp') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Denda Pelunasan Cepat <span class="text-xs text-gray-400 font-normal">(bp)</span>
                            </label>
                            <input type="number" name="early_repayment_rate_bp"
                                   value="{{ old('early_repayment_rate_bp', $loanProduct->early_repayment_rate_bp ?? 500) }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   min="0" max="10000">
                            <div class="text-xs text-gray-400 mt-1">Default: 500 bp = 5.00% dari sisa</div>
                            @error('early_repayment_rate_bp') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Mapping Akun Buku Besar --}}
                <div class="border-t border-gray-100 pt-4">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Mapping Akun Buku Besar</div>
                    <p class="text-xs text-gray-400 mb-3">Opsional — jika diisi, jurnal akan menggunakan akun spesifik produk ini. Kosongkan untuk pakai default global (4111/4112/4113).</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Akun Piutang (Receivable) <span class="text-xs text-gray-400 font-normal">(ASSET)</span>
                            </label>
                            <select name="receivable_account_id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <option value="">— Tidak diubah / Default —</option>
                                @foreach($assetAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected(old('receivable_account_id', $loanProduct->receivable_account_id) == $acc->id)>
                                        {{ $acc->code }} – {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($loanProduct->receivableAccount)
                                <p class="text-xs text-gray-400 mt-1">Saat ini: {{ $loanProduct->receivableAccount->code }} – {{ $loanProduct->receivableAccount->name }}</p>
                            @endif
                            @error('receivable_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Akun Bunga</label>
                                <select name="interest_income_account_id"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <option value="">— Default 4111 —</option>
                                    @foreach($revenueAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('interest_income_account_id', $loanProduct->interest_income_account_id) == $acc->id)>
                                            {{ $acc->code }} – {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('interest_income_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Akun Admin</label>
                                <select name="admin_income_account_id"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <option value="">— Default 4112 —</option>
                                    @foreach($revenueAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('admin_income_account_id', $loanProduct->admin_income_account_id) == $acc->id)>
                                            {{ $acc->code }} – {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('admin_income_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Akun Asuransi</label>
                                <select name="insurance_income_account_id"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    <option value="">— Default 4113 —</option>
                                    @foreach($revenueAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('insurance_income_account_id', $loanProduct->insurance_income_account_id) == $acc->id)>
                                            {{ $acc->code }} – {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('insurance_income_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $loanProduct->is_active))
                           class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Update
                    </button>
                    <a href="{{ route('admin.master.loan_products.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
