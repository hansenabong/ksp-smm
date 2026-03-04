<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Jenis Pinjaman</h2>
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

                <form method="POST" action="{{ route('admin.master.loan_products.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm">Nama</label>
                        <input name="name" value="{{ old('name') }}"
                               class="w-full border rounded px-3 py-2"
                               placeholder="Pinjaman Flat" required>
                    </div>

                    <div>
                        <label class="text-sm">Metode</label>
                        <select name="method" class="w-full border rounded px-3 py-2" required>
                            @foreach($methods as $m)
                                <option value="{{ $m }}" @selected(old('method','FLAT') === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm">Bunga per bulan (Basis Points)</label>
                        <input type="number" name="interest_rate_monthly_bp" value="{{ old('interest_rate_monthly_bp', 0) }}"
                               class="w-full border rounded px-3 py-2"
                               placeholder="contoh: 200 = 2.00%/bulan" required>
                        <div class="text-xs text-gray-500 mt-1">Contoh: 200 = 2.00% per bulan</div>
                    </div>

                    <div>
                        <label class="text-sm">Biaya Admin (Rp)</label>
                        <input type="number" name="admin_fee" value="{{ old('admin_fee', 0) }}"
                               class="w-full border rounded px-3 py-2"
                               placeholder="contoh: 10000">
                    </div>

                    <div>
                        <label class="text-sm">Max Tenor (bulan)</label>
                        <input type="number" name="max_tenor_months" value="{{ old('max_tenor_months', 12) }}"
                               class="w-full border rounded px-3 py-2" required>
                    </div>

                    {{-- checkbox boolean safe --}}
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        <span class="text-sm">Aktif</span>
                    </label>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                        <a href="{{ route('admin.master.loan_products.index') }}"
                           class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>

            </div>
</x-admin-layout>