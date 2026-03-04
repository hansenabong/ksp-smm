<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Kas / Bank</h2>
            <a href="{{ route('admin.master.cash_accounts.index') }}"
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
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Edit: {{ $cashAccount->name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Setiap kas/bank harus dipetakan ke akun buku besar agar jurnal otomatis berjalan.</div>
            </div>

            <form method="POST" action="{{ route('admin.master.cash_accounts.update', $cashAccount) }}" class="px-5 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Kas / Bank <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name', $cashAccount->name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           required>
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <select name="type"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            required>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('type', $cashAccount->type) == $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Akun Buku Besar (Ledger) <span class="text-red-500">*</span>
                    </label>
                    <select name="ledger_account_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            required>
                        <option value="">— Pilih Akun Ledger —</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}"
                                    @selected(old('ledger_account_id', $cashAccount->ledger_account_id) == $acc->id)>
                                {{ $acc->code }} – {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Pilih akun yang mewakili kas/bank ini di Buku Besar (misal: 1-101 Kas Tunai, 1-102 Bank BRI).
                    </p>
                    @error('ledger_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $cashAccount->is_active))
                           class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Update
                    </button>
                    <a href="{{ route('admin.master.cash_accounts.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
