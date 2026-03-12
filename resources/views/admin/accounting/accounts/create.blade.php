<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Akuntansi / COA</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Akun</h2>
            </div>
            <a href="{{ route('admin.accounting.accounts.index') }}"
               class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg px-3 py-1.5 transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Data Akun Baru</h3>
                <span class="text-xs text-gray-400">* Wajib diisi</span>
            </div>
            <form method="POST" action="{{ route('admin.accounting.accounts.store') }}" class="px-5 py-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kode Akun <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: 1110, 4111" required>
                    @error('code')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Akun <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: Kas, Pendapatan Bunga Pinjaman" required>
                    @error('name')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Tipe Akun <span class="text-red-500">*</span>
                        </label>
                        <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                            <option value="">— Pilih —</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>
                                    {{ match($t) {
                                        'ASSET'     => 'ASSET — Aset',
                                        'LIABILITY' => 'LIABILITY — Liabilitas',
                                        'EQUITY'    => 'EQUITY — Ekuitas',
                                        'REVENUE'   => 'REVENUE — Pendapatan',
                                        'EXPENSE'   => 'EXPENSE — Beban',
                                    } }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Saldo Normal <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="normal_balance" value="D" {{ old('normal_balance') === 'D' ? 'checked' : '' }} class="text-indigo-600">
                                <span class="text-sm text-gray-700">Debit (D)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="normal_balance" value="C" {{ old('normal_balance') === 'C' ? 'checked' : '' }} class="text-indigo-600">
                                <span class="text-sm text-gray-700">Kredit (C)</span>
                            </label>
                        </div>
                        @error('normal_balance')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Parent Akun <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="parent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Tidak ada (akun root) —</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->code }} — {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                        Simpan Akun
                    </button>
                    <a href="{{ route('admin.accounting.accounts.index') }}"
                       class="px-5 py-2 border border-gray-200 text-gray-600 hover:text-gray-800 text-sm rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
