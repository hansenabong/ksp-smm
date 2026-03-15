<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Legal / Tambah Berkas</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Berkas Baru</h2>
            </div>
            <a href="{{ route('admin.legal.index') }}"
               class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Informasi Berkas</h3>
                <span class="text-xs text-gray-400">* Wajib diisi</span>
            </div>

            <form method="POST" action="{{ route('admin.legal.store') }}" enctype="multipart/form-data" class="px-5 py-5 space-y-4">
                @csrf

                {{-- Judul --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Judul Berkas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="Contoh: Dokumen Agunan - Budi Santoso"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('title')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Deskripsi <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="description" rows="2"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">{{ old('description') }}</textarea>
                    @error('description')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Anggota --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Anggota Terkait <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select name="member_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <option value="">— Tidak terkait anggota —</option>
                        @foreach($members as $m)
                        <option value="{{ $m->id }}" {{ old('member_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->member_no }} — {{ $m->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Upload Dokumen & Foto <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-400 mb-2">Pilih satu atau lebih file sekaligus (JPG, PNG, PDF, DOC/DOCX, maks 10MB per file)</p>

                    <div x-data="{ files: [] }" class="space-y-2">
                        <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition">
                            <svg class="w-8 h-8 text-gray-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span class="text-xs text-gray-400">Klik untuk pilih file</span>
                            <input type="file" name="files[]" multiple required accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="hidden"
                                   @change="files = Array.from($event.target.files)">
                        </label>

                        <template x-if="files.length > 0">
                            <div class="bg-gray-50 rounded-lg p-3 space-y-1">
                                <div class="text-xs font-semibold text-gray-600 mb-1">
                                    <span x-text="files.length"></span> file dipilih:
                                </div>
                                <template x-for="(file, idx) in files" :key="idx">
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span x-text="file.name" class="truncate"></span>
                                        <span class="text-gray-300 flex-shrink-0" x-text="(file.size / 1024).toFixed(0) + ' KB'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    @error('files')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    @error('files.*')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition">
                        Simpan Berkas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
