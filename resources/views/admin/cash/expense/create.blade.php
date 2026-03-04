<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengeluaran Kas</h2>
    </x-slot>

            <div class="bg-white p-6 rounded shadow">

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        <div class="font-semibold mb-1">Ada error:</div>
                        <ul class="list-disc ml-5 text-sm">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.cash.expense.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm">Tanggal</label>
                        <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                               class="w-full border rounded px-3 py-2">
                        @error('trx_date') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Dari Kas</label>
                        <select name="from_cash_account_id" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Kas</option>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('from_cash_account_id') == $acc->id)>
                                    {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('from_cash_account_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Kategori</label>
                        <select name="category" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" @selected(old('category') == $cat)>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('category') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Jumlah (Rp)</label>
                        <input type="number" name="amount" value="{{ old('amount') }}"
                               class="w-full border rounded px-3 py-2" min="1">
                        @error('amount') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Uraian (optional)</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="w-full border rounded px-3 py-2" maxlength="255">
                        @error('description') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                        <a href="{{ route('admin.cash.expense.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>
            </div>
</x-admin-layout>