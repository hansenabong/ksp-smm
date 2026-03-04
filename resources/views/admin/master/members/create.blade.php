<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Anggota</h2>
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

                <form method="POST" action="{{ route('admin.master.members.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm">Member No</label>
                        <input name="member_no" value="{{ old('member_no') }}"
                               class="w-full border rounded px-3 py-2" placeholder="A001" required>
                        @error('member_no') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Nama</label>
                        <input name="name" value="{{ old('name') }}"
                               class="w-full border rounded px-3 py-2" required>
                        @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Phone (opsional)</label>
                        <input name="phone" value="{{ old('phone') }}"
                               class="w-full border rounded px-3 py-2">
                        @error('phone') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Alamat (opsional)</label>
                        <textarea name="address" rows="3"
                                  class="w-full border rounded px-3 py-2">{{ old('address') }}</textarea>
                        @error('address') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span class="text-sm">Aktif</span>
                    </div>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                        <a href="{{ route('admin.master.members.index') }}"
                           class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>

            </div>
</x-admin-layout>