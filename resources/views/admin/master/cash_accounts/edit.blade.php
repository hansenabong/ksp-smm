<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Kas / Rekening</h2>
            <a href="{{ route('admin.master.cash_accounts.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl">

        @if($errors->any())
            <div class="mb-4 flex items-start gap-2 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="list-disc ml-3 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-sm font-semibold text-gray-700">Edit: {{ $cashAccount->name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Perubahan nama akan disinkronkan ke akun buku besar terkait.</div>
            </div>

            @php
                $typeDescs = [
                    'CASH'  => ['label' => 'Kas Tunai',     'hint' => 'Uang tunai · ledger 111x', 'icon' => '💵'],
                    'BANK'  => ['label' => 'Rekening Bank', 'hint' => 'Simpanan bank · ledger 112x', 'icon' => '🏦'],
                    'CHECK' => ['label' => 'Cek',           'hint' => 'Cek diterima · ledger 114x', 'icon' => '📄'],
                    'GIRO'  => ['label' => 'Bilyet / Giro', 'hint' => 'Bilyet Giro · ledger 115x', 'icon' => '📋'],
                ];
                $ledgerPrefixes = ['CASH' => '111x', 'BANK' => '112x', 'CHECK' => '114x', 'GIRO' => '115x'];
            @endphp

            <form method="POST" action="{{ route('admin.master.cash_accounts.update', $cashAccount) }}" class="px-5 py-5 space-y-4"
                  x-data="{ type: '{{ old('type', $cashAccount->type) }}' }">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Nama Rekening <span class="text-red-500">*</span>
                    </label>
                    <input name="name" value="{{ old('name', $cashAccount->name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           required>
                    @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Tipe <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($typeDescs as $val => $info)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $val }}"
                                   x-model="type"
                                   class="sr-only">
                            <div class="flex items-start gap-2.5 p-3 border-2 rounded-xl transition"
                                 :class="type === '{{ $val }}'
                                     ? '{{ $val === 'CASH' ? 'border-amber-400 bg-amber-50' : ($val === 'BANK' ? 'border-blue-400 bg-blue-50' : ($val === 'CHECK' ? 'border-purple-400 bg-purple-50' : 'border-teal-400 bg-teal-50')) }}'
                                     : 'border-gray-200 bg-white hover:border-gray-300'">
                                <span class="text-lg leading-none mt-0.5">{{ $info['icon'] }}</span>
                                <div>
                                    <div class="text-sm font-semibold text-gray-800">{{ $info['label'] }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $info['hint'] }}</div>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('type') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Ledger account: read-only display --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                        Akun Buku Besar (Ledger)
                    </label>
                    @if($cashAccount->ledgerAccount)
                        <div class="w-full border border-gray-100 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-700">
                            <span class="font-mono text-xs text-gray-500">{{ $cashAccount->ledgerAccount->code }}</span>
                            — {{ $cashAccount->ledgerAccount->name }}
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Akun ledger dibuat otomatis dan tidak dapat diubah di sini.</p>
                    @else
                        @php $prefix = $ledgerPrefixes[$cashAccount->type] ?? '???'; @endphp
                        <div class="w-full border border-amber-200 bg-amber-50 rounded-lg px-3 py-2 text-sm text-amber-800">
                            Belum dipetakan. Klik <strong>Update</strong> — akun ledger <span class="font-mono">{{ $prefix }}</span> akan dibuat otomatis.
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $cashAccount->is_active))
                           class="rounded border-gray-300">
                    <label for="is_active" class="text-sm text-gray-700">Aktif</label>
                </div>

                <div class="pt-2 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        Update
                    </button>
                    <a href="{{ route('admin.master.cash_accounts.index') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-admin-layout>
