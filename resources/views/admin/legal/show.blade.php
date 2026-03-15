<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Legal / Berkas</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $berkas->title }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.legal.destroy', $berkas) }}"
                      onsubmit="return confirm('Hapus berkas ini beserta semua file-nya?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs text-red-600 hover:text-red-800 border border-red-200 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus Berkas
                    </button>
                </form>
                <a href="{{ route('admin.legal.index') }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Informasi Berkas</h3>
            </div>
            <div class="px-5 py-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Judul</div>
                    <div class="font-medium text-gray-800">{{ $berkas->title }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Anggota</div>
                    <div class="text-gray-700">
                        @if($berkas->member)
                            {{ $berkas->member->member_no }} — {{ $berkas->member->name }}
                        @else
                            <span class="text-gray-300">Tidak terkait anggota</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Dibuat Oleh</div>
                    <div class="text-gray-700">{{ $berkas->creator?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tanggal</div>
                    <div class="text-gray-700">{{ $berkas->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($berkas->description)
                <div class="col-span-2">
                    <div class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Deskripsi</div>
                    <div class="text-gray-700">{{ $berkas->description }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Files --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">
                    Dokumen & Foto
                    <span class="text-xs font-normal text-gray-400 ml-1">({{ $berkas->documents->count() }} file)</span>
                </h3>
            </div>

            <div class="p-5">
                @if($berkas->documents->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($berkas->documents as $doc)
                    <div class="group relative border border-gray-200 rounded-lg overflow-hidden hover:border-indigo-300 transition">
                        {{-- Preview --}}
                        @if($doc->isImage())
                        <div class="aspect-[4/3] bg-gray-100">
                            <img src="{{ asset('storage/' . $doc->file_path) }}"
                                 alt="{{ $doc->original_name }}"
                                 class="w-full h-full object-cover">
                        </div>
                        @else
                        <div class="aspect-[4/3] bg-gray-50 flex flex-col items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span class="text-xs text-gray-400 uppercase font-semibold">
                                {{ pathinfo($doc->original_name, PATHINFO_EXTENSION) }}
                            </span>
                        </div>
                        @endif

                        {{-- Info bar --}}
                        <div class="px-2 py-2 bg-white border-t border-gray-100">
                            <div class="text-xs text-gray-700 truncate font-medium" title="{{ $doc->original_name }}">
                                {{ $doc->original_name }}
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-[10px] text-gray-400">{{ number_format($doc->file_size / 1024, 1) }} KB</span>
                                <div class="flex gap-1">
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                       class="text-[10px] text-indigo-600 hover:underline">Unduh</a>
                                    <form method="POST" action="{{ route('admin.legal.file.destroy', [$berkas, $doc]) }}"
                                          onsubmit="return confirm('Hapus file ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-[10px] text-red-500 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-gray-400 text-sm">Belum ada file dalam berkas ini.</div>
                @endif
            </div>

            {{-- Add more files --}}
            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 rounded-b-xl">
                <form method="POST" action="{{ route('admin.legal.addFiles', $berkas) }}" enctype="multipart/form-data"
                      class="flex items-center gap-3" x-data="{ count: 0 }">
                    @csrf
                    <label class="flex-1 flex items-center gap-2 cursor-pointer px-3 py-2 border border-dashed border-gray-300 rounded-lg hover:border-indigo-400 hover:bg-white transition">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-xs text-gray-500" x-text="count > 0 ? count + ' file dipilih' : 'Tambah file...'"></span>
                        <input type="file" name="files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="hidden"
                               @change="count = $event.target.files.length">
                    </label>
                    <button type="submit" x-show="count > 0"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                        Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
