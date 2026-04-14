<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Anggota</h2>
            <a href="{{ route('admin.master.members.index') }}"
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
                <div class="text-sm font-semibold text-gray-700">Data Anggota</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST" action="{{ route('admin.master.members.store') }}" class="px-5 py-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        No. Anggota <span class="text-red-500">*</span>
                    </label>
                    <input name="member_no" value="{{ old('member_no') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: A001" required>
                    @error('member_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Lengkap (sesuai KTP) <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="Nama lengkap" required>
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Jenis Kelamin <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <select name="gender" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">— Pilih —</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                        @error('gender') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            No. KTP <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input name="ktp_no" value="{{ old('ktp_no') }}" maxlength="20"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                               placeholder="16 digit NIK">
                        @error('ktp_no') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Tgl. Lahir <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        @error('birth_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Berlaku KTP <span class="text-xs text-gray-400 font-normal">(kosong = seumur hidup)</span>
                        </label>
                        <input type="date" name="ktp_expiry" value="{{ old('ktp_expiry') }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        @error('ktp_expiry') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Status Perkawinan <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="marital_status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Pilih —</option>
                        <option value="BELUM_KAWIN" @selected(old('marital_status') === 'BELUM_KAWIN')>Belum Kawin</option>
                        <option value="KAWIN" @selected(old('marital_status') === 'KAWIN')>Kawin</option>
                        <option value="CERAI_HIDUP" @selected(old('marital_status') === 'CERAI_HIDUP')>Cerai Hidup</option>
                        <option value="CERAI_MATI" @selected(old('marital_status') === 'CERAI_MATI')>Cerai Mati</option>
                    </select>
                    @error('marital_status') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        No. Telepon <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input name="phone" value="{{ old('phone') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: 08123456789">
                    @error('phone') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Alamat <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="address" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                              placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                    @error('address') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kota <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <input name="city" value="{{ old('city') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: Jakarta">
                    @error('city') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', true))
                           class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Simpan
                    </button>
                    <a href="{{ route('admin.master.members.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
