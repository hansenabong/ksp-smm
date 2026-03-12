<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ganti Password</h2>
    </x-slot>

    <div class="max-w-md">

        @if(session('status') === 'password-updated')
            <div class="mb-4 flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Password berhasil diperbarui.
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Ubah Password Akun</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    Gunakan password yang kuat dan unik. Minimal 8 karakter. <span class="text-red-500">*</span> Wajib diisi
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="px-5 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           required>
                    @error('current_password', 'updatePassword')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           required>
                    <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter, kombinasikan huruf, angka, dan simbol.</p>
                    @error('password', 'updatePassword')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           required>
                    @error('password_confirmation', 'updatePassword')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>

        {{-- Admin: reset password pengguna lain --}}
        @if(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN']))
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-amber-100 bg-amber-50">
                <div class="text-sm font-semibold text-amber-800">Reset Password Pengguna Lain</div>
                <div class="text-xs text-amber-600 mt-0.5">
                    Hanya SUPER_ADMIN dan ADMIN yang dapat mereset password pengguna lain.
                </div>
            </div>
            <div class="px-5 py-4 text-sm text-gray-600">
                Untuk mereset password pengguna lain, buka halaman
                <a href="{{ route('admin.users.index') }}" class="text-indigo-600 font-semibold hover:underline">Manajemen User</a>,
                klik <strong>Edit</strong> pada pengguna yang bersangkutan, lalu isi kolom Password Baru.
            </div>
        </div>
        @endif

    </div>
</x-admin-layout>
