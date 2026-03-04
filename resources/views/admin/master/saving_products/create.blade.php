<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Jenis Simpanan</h2>
    </x-slot>

            <div class="bg-white p-6 rounded shadow space-y-4">

                @if($errors->any())
                    <div class="p-3 bg-red-100 text-red-800 rounded">
                        <div class="font-semibold mb-1">Ada error:</div>
                        <ul class="list-disc ml-5 text-sm">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.master.saving_products.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm">Nama</label>
                        <input name="name" value="{{ old('name') }}"
                               class="w-full border rounded px-3 py-2"
                               placeholder="Simpanan Pokok" required>
                        @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>
                    <input type="hidden" name="is_withdrawable" value="0">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_withdrawable" value="1" @checked(old('is_withdrawable', true))>
                        <span class="text-sm">Bisa ditarik (withdraw)</span>
                    </div>
                    <input type="hidden" name="is_withdrawable" value="0">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span class="text-sm">Aktif</span>
                    </div>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                        <a href="{{ route('admin.master.saving_products.index') }}"
                           class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>

            </div>
</x-admin-layout>