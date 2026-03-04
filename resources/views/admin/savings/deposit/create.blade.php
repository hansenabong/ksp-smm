<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Setoran Simpanan</h2>
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

                <form method="POST" action="{{ route('admin.savings.deposit.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="text-sm">Tanggal</label>
                        <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                               class="w-full border rounded px-3 py-2">
                        @error('trx_date') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Anggota</label>
                        <select name="member_id" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Anggota</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected(old('member_id') == $m->id)>
                                    {{ $m->member_no ?? $m->id }} - {{ $m->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('member_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Jenis Simpanan</label>
                        <select name="saving_product_id" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Jenis Simpanan</option>
                            @foreach($savingProducts as $sp)
                                <option value="{{ $sp->id }}" @selected(old('saving_product_id') == $sp->id)>
                                    {{ $sp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('saving_product_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-sm">Kas Tujuan</label>
                        <select name="cash_account_id" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Kas</option>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" @selected(old('cash_account_id') == $acc->id)>
                                    {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('cash_account_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
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
                        <a href="{{ route('admin.savings.deposit.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>
            </div>
</x-admin-layout>