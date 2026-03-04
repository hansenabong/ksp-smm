<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Jenis Pinjaman</h2>
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

                <form method="POST" action="{{ route('admin.master.loan_products.update', $loanProduct) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="text-sm">Nama</label>
                        <input name="name" value="{{ old('name', $loanProduct->name) }}"
                               class="w-full border rounded px-3 py-2" required>
                    </div>

                    <div>
                        <label class="text-sm">Metode</label>
                        <select name="method" class="w-full border rounded px-3 py-2" required>
                            @foreach($methods as $m)
                                <option value="{{ $m }}" @selected(old('method', $loanProduct->method) === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm">Bunga per bulan (Basis Points)</label>
                        <input type="number" name="interest_rate_monthly_bp" value="{{ old('interest_rate_monthly_bp', $loanProduct->interest_rate_monthly_bp) }}"
                               class="w-full border rounded px-3 py-2" required>
                        <div class="text-xs text-gray-500 mt-1">Contoh: 200 = 2.00% per bulan</div>
                    </div>

                    <div>
                        <label class="text-sm">Biaya Admin (Rp)</label>
                        <input type="number" name="admin_fee" value="{{ old('admin_fee', $loanProduct->admin_fee) }}"
                               class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="text-sm">Max Tenor (bulan)</label>
                        <input type="number" name="max_tenor_months" value="{{ old('max_tenor_months', $loanProduct->max_tenor_months) }}"
                               class="w-full border rounded px-3 py-2" required>
                    </div>

                    <input type="hidden" name="is_active" value="0">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $loanProduct->is_active))>
                        <span class="text-sm">Aktif</span>
                    </label>

                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Update</button>
                        <a href="{{ route('admin.master.loan_products.index') }}"
                           class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                    </div>
                </form>

            </div>
</x-admin-layout>