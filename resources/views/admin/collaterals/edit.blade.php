<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Agunan</h2>
            <a href="{{ route('admin.loans.applications.collaterals.index', $application) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        @if($errors->any())
            <div class="mb-4 flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc ml-4 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Data Agunan</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST"
                  action="{{ route('admin.loans.applications.collaterals.update', [$application, $collateral]) }}"
                  enctype="multipart/form-data"
                  class="px-5 py-5 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Jenis Agunan <span class="text-red-500">*</span>
                    </label>
                    <select name="type"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        <option value="">— Pilih Jenis —</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected(old('type', $collateral->type) === $t)>
                                @if($t === 'SERTIPIKAT') Sertipikat / SHM
                                @elseif($t === 'BPKB') BPKB Kendaraan
                                @elseif($t === 'DEPOSITO') Deposito
                                @else Lainnya
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('type') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Deskripsi <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <textarea name="description" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                              maxlength="500" placeholder="Mis: No. SHM, tahun kendaraan, dll.">{{ old('description', $collateral->description) }}</textarea>
                    @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nilai Taksiran (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" data-money name="estimated_value"
                           value="{{ old('estimated_value', $collateral->estimated_value) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="0" required onclick="this.select()">
                    @error('estimated_value') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Dokumen <span class="text-xs text-gray-400 font-normal">(opsional, maks. 20 MB)</span>
                    </label>
                    @if($collateral->document_path)
                        <div class="mb-2 text-xs text-gray-500">
                            Dokumen saat ini:
                            <a href="{{ route('admin.loans.applications.collaterals.download', [$application, $collateral]) }}"
                               class="text-indigo-600 hover:underline">{{ $collateral->document_name ?? 'Unduh' }}</a>
                            — upload file baru untuk mengganti
                        </div>
                    @endif
                    <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('document') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.loans.applications.collaterals.index', $application) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
