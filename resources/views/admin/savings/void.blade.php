<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">VOID Transaksi Simpanan</h2>
    </x-slot>
    <div class="space-y-4">

            @if (session('success'))
                <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 bg-red-100 text-red-800 rounded">
                    <div class="font-semibold mb-1">Ada error:</div>
                    <ul class="list-disc ml-5 text-sm">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-6 rounded shadow text-sm space-y-2">
                <div><span class="text-gray-500">ID:</span> <b>#{{ $transaction->id }}</b></div>
                <div><span class="text-gray-500">Tanggal:</span> <b>{{ $transaction->trx_date }}</b></div>
                <div><span class="text-gray-500">Tipe:</span> <b>{{ $transaction->type }}</b></div>
                <div><span class="text-gray-500">Anggota:</span>
                    <b>{{ $transaction->member?->member_no ?? $transaction->member_id }} -
                        {{ $transaction->member?->name }}</b>
                </div>
                <div><span class="text-gray-500">Jenis Simpanan:</span> <b>{{ $transaction->savingProduct?->name }}</b>
                </div>
                <div><span class="text-gray-500">Kas:</span> <b>{{ $transaction->cashAccount?->name }}</b></div>
                <div><span class="text-gray-500">Jumlah:</span> <b>Rp
                        {{ number_format($transaction->amount, 0, ',', '.') }}</b></div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <form method="POST" action="{{ route('admin.savings.void.store', $transaction) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-sm">Alasan VOID</label>
                        <input type="text" name="void_reason" class="w-full border rounded px-3 py-2" maxlength="255"
                            required>
                        @error('void_reason')
                            <div class="text-red-600 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                    @php($returnUrl = request('return') ?? route($transaction->type === 'DEPOSIT' ? 'admin.savings.deposit.index' : 'admin.savings.withdraw.index'))

                    <input type="hidden" name="return" value="{{ $returnUrl }}">

                    <div class="flex gap-2">
                        @if ($transaction->is_voided)
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">VOID</span>
                            <a href="{{ $returnUrl }}" class="px-4 py-2 bg-gray-200 rounded">Kembali</a>
                        @else
                            <button class="px-4 py-2 bg-red-600 text-white rounded">VOID</button>
                            <a href="{{ $returnUrl }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                        @endif
                    </div>
                </form>
            </div>

    </div>
</x-app-layout>
