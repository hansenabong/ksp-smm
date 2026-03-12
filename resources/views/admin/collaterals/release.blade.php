<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lepas Agunan</h2>
            <a href="{{ route('admin.loans.applications.collaterals.index', $application) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">
        {{-- Collateral info --}}
        <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800">
            <p class="font-semibold">Agunan: {{ $collateral->type_label }}</p>
            @if($collateral->description)
                <p class="mt-0.5 text-xs text-amber-700">{{ $collateral->description }}</p>
            @endif
            <p class="mt-1 text-xs">Nilai taksiran: <strong>Rp {{ number_format($collateral->estimated_value, 0, ',', '.') }}</strong></p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc ml-4 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Konfirmasi Pelepasan Agunan</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>

            <form method="POST"
                  action="{{ route('admin.loans.applications.collaterals.release', [$application, $collateral]) }}"
                  class="px-5 py-5 space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tanggal Pelepasan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="released_at" value="{{ old('released_at', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                    @error('released_at') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Catatan Pelepasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="release_note" rows="3"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                              maxlength="500" placeholder="Alasan / keterangan pelepasan agunan" required>{{ old('release_note') }}</textarea>
                    @error('release_note') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                        Konfirmasi Pelepasan
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
