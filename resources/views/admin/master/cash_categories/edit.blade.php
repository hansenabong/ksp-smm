<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Master Data / Kategori Kas</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Kategori Kas</h2>
            </div>
            <a href="{{ route('admin.master.cash_categories.index') }}"
               class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Edit Kategori</span>
                <span class="text-xs text-gray-400">* Wajib diisi</span>
            </div>

            @if($errors->any())
                <div class="mx-5 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.master.cash_categories.update', $cashCategory) }}" class="p-5 space-y-4"
                  x-data="{
                      type: '{{ old('type', $cashCategory->type) }}',
                      incomeAccounts: @js($incomeAccounts),
                      expenseAccounts: @js($expenseAccounts),
                      selectedAccount: '{{ old('account_id', $cashCategory->account_id) }}',
                      accounts: [],
                      isTransfer: false,
                      init() {
                          this.updateAccounts(this.type);
                          this.$watch('type', (val) => this.updateAccounts(val));
                      },
                      updateAccounts(t) {
                          this.isTransfer = (t === 'TRANSFER');
                          this.accounts = t === 'INCOME' ? this.incomeAccounts
                                        : t === 'EXPENSE' ? this.expenseAccounts
                                        : [];
                      }
                  }">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select name="type" x-model="type" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $cashCategory->name) }}" required maxlength="100"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Account field: required for INCOME/EXPENSE, hidden for TRANSFER --}}
                <div x-show="!isTransfer">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Akun Ledger (Lawan) <span class="text-red-500">*</span>
                    </label>
                    <select name="account_id" x-model="selectedAccount" :disabled="isTransfer"
                            :required="!isTransfer"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Pilih Akun —</option>
                        <template x-for="acc in accounts" :key="acc.id">
                            <option :value="acc.id" :selected="selectedAccount == acc.id"
                                    x-text="acc.code + ' — ' + acc.name + ' (' + acc.type + ')'"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        <span x-show="type === 'INCOME'">Pilih akun Ekuitas (3xxx) atau Pendapatan (4xxx) — akun lawan saat kas masuk.</span>
                        <span x-show="type === 'EXPENSE'">Pilih akun Beban (5xxx) — akun lawan saat kas keluar.</span>
                    </p>
                    @error('account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div x-show="isTransfer"
                     class="px-3 py-2.5 bg-blue-50 border border-blue-100 rounded-lg text-xs text-blue-700">
                    Transfer antar kas tidak memerlukan akun lawan — jurnal posting otomatis antar akun kas.
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Keterangan <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="description" value="{{ old('description', $cashCategory->description) }}" maxlength="255"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Urutan Tampil <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $cashCategory->sort_order) }}" min="0"
                           class="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('sort_order') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Perbarui
                    </button>
                    <a href="{{ route('admin.master.cash_categories.index') }}"
                       class="px-5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
