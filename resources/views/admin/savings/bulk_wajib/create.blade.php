<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Simpanan Wajib Massal</h2>
            <a href="{{ route('admin.savings.deposit.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    @if($errors->any())
        <div class="mb-4 flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            <ul class="list-disc ml-4 space-y-0.5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div x-data="{
        defaultAmount: 0,
        applyDefault() {
            document.querySelectorAll('.amount-input').forEach(el => {
                if (!el.value || el.value == '0') el.value = this.defaultAmount;
            });
        },
        fillAll() {
            document.querySelectorAll('.amount-input').forEach(el => {
                el.value = this.defaultAmount;
            });
        },
        clearAll() {
            document.querySelectorAll('.amount-input').forEach(el => el.value = '');
        },
        total: 0,
        count: 0,
        recalc() {
            let t = 0, c = 0;
            document.querySelectorAll('.amount-input').forEach(el => {
                const v = parseInt(el.value) || 0;
                if (v > 0) { t += v; c++; }
            });
            this.total = t;
            this.count = c;
        }
    }" @input.debounce.100ms="recalc()" @change="recalc()">

    <form method="POST" action="{{ route('admin.savings.bulk_wajib.store') }}">
        @csrf

        {{-- Header config --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Konfigurasi Setoran</div>
                <div class="text-xs text-gray-400 mt-0.5"><span class="text-red-500">*</span> Wajib diisi</div>
            </div>
            <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                    @error('trx_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Jenis Simpanan <span class="text-red-500">*</span>
                    </label>
                    <select name="saving_product_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        <option value="">— Pilih —</option>
                        @foreach($savingProducts as $sp)
                            <option value="{{ $sp->id }}" @selected(old('saving_product_id') == $sp->id)>{{ $sp->name }}</option>
                        @endforeach
                    </select>
                    @error('saving_product_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Kas Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select name="cash_account_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300" required>
                        <option value="">— Pilih —</option>
                        @foreach($cashAccounts as $acc)
                            <option value="{{ $acc->id }}" @selected(old('cash_account_id') == $acc->id)>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                    @error('cash_account_id') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Quick-fill toolbar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-3 mb-3 flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Isi cepat:</span>
            <div class="flex items-center gap-2">
                <input type="number" x-model.number="defaultAmount" min="0" placeholder="Jumlah default"
                       class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-gray-300">
                <button type="button" @click="fillAll()"
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                    Isi Semua
                </button>
                <button type="button" @click="clearAll()"
                        class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                    Kosongkan
                </button>
            </div>
            <div class="ml-auto text-right">
                <span class="text-xs text-gray-500" x-text="`${count} anggota · Rp ${total.toLocaleString('id-ID')}`"></span>
            </div>
        </div>

        {{-- Member table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">Daftar Anggota Aktif ({{ $members->count() }})</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-2 text-left font-semibold w-8">#</th>
                            <th class="px-4 py-2 text-left font-semibold">No. Anggota</th>
                            <th class="px-4 py-2 text-left font-semibold">Nama</th>
                            <th class="px-4 py-2 text-right font-semibold w-44">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($members as $i => $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs font-mono">{{ $m->member_no }}</td>
                            <td class="px-4 py-2 text-gray-800 font-medium">{{ $m->name }}</td>
                            <td class="px-4 py-2">
                                <input type="number"
                                       name="amounts[{{ $m->id }}]"
                                       value="{{ old("amounts.{$m->id}", '') }}"
                                       min="0"
                                       placeholder="0"
                                       class="amount-input w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Total: <span class="font-bold text-gray-800" x-text="`Rp ${total.toLocaleString('id-ID')}`">Rp 0</span>
                untuk <span class="font-bold" x-text="count">0</span> anggota
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                Simpan Semua Setoran
            </button>
        </div>
    </form>

    </div>
</x-admin-layout>
