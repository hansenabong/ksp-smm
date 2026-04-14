<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Deposito {{ $deposito->reference_code }}
            </h2>
            <a href="{{ route('admin.deposito.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    @php
        $disp = $deposito->displayStatus();
        $statusClass = match(true) {
            $disp === 'JATUH TEMPO' => 'bg-amber-100 text-amber-800',
            $disp === 'ACTIVE'      => 'bg-emerald-100 text-emerald-800',
            $disp === 'WITHDRAWN'   => 'bg-gray-200 text-gray-700',
            default                 => 'bg-gray-100 text-gray-600',
        };
        $isMatured = $deposito->isMatured();
        $canCair = $deposito->status === \App\Models\DepositoAccount::STATUS_ACTIVE && $isMatured;
        $daysLeft = $deposito->daysToMaturity();
        $member = $deposito->member;
    @endphp

    <div class="space-y-4">

        @if(session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc ml-3 space-y-0.5">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Header card with status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Anggota</div>
                    <div class="text-lg font-bold text-gray-800">{{ $member->name }}</div>
                    <div class="text-xs text-gray-500">{{ $member->member_no }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Produk</div>
                    <div class="text-sm font-semibold text-gray-800">{{ $deposito->savingProduct->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Status</div>
                    <span class="inline-block mt-1 px-3 py-1 text-xs font-bold rounded {{ $statusClass }}">{{ $disp }}</span>
                </div>
            </div>
        </div>

        {{-- Data Pribadi + Rekening Bank --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Data Pribadi --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Data Pribadi</div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nama Lengkap</dt>
                        <dd class="font-medium text-gray-800">{{ $member->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Jenis Kelamin</dt>
                        <dd class="font-medium text-gray-800">
                            @if($member->gender === 'L') Laki-laki
                            @elseif($member->gender === 'P') Perempuan
                            @else — @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">No. KTP</dt>
                        <dd class="font-mono font-medium text-gray-800">{{ $member->ktp_no ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tgl. Lahir</dt>
                        <dd class="font-medium text-gray-800">{{ $member->birth_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Berlaku KTP</dt>
                        <dd class="font-medium text-gray-800">{{ $member->ktp_expiry?->format('d/m/Y') ?? 'Seumur Hidup' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-medium text-gray-800">
                            @php
                                $statusMap = ['BELUM_KAWIN'=>'Belum Kawin','KAWIN'=>'Kawin','CERAI_HIDUP'=>'Cerai Hidup','CERAI_MATI'=>'Cerai Mati'];
                            @endphp
                            {{ $statusMap[$member->marital_status] ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="font-medium text-gray-800 text-right max-w-[60%]">{{ $member->address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Hp</dt>
                        <dd class="font-medium text-gray-800">{{ $member->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Rekening Bank --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Rekening Bank</div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nama Pemilik</dt>
                        <dd class="font-medium text-gray-800">{{ $deposito->bank_account_name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">No. Rekening</dt>
                        <dd class="font-mono font-medium text-gray-800">{{ $deposito->bank_account_no ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nama Bank</dt>
                        <dd class="font-medium text-gray-800">{{ $deposito->bank_name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Numbers grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Pokok</div>
                <div class="text-base font-bold text-gray-900 tabular-nums">Rp {{ number_format($deposito->principal_amount, 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Bunga / Tenor</div>
                <div class="text-base font-bold text-gray-900">
                    {{ rtrim(rtrim(number_format((float)$deposito->interest_rate_pct, 2, ',', '.'), '0'), ',') }}% / {{ $deposito->term_months }} bln
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Bunga Terakumulasi (saat jatuh tempo)</div>
                <div class="text-base font-bold text-emerald-700 tabular-nums">Rp {{ number_format($deposito->interestAmount(), 0, ',', '.') }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3">
                <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Total Payout</div>
                <div class="text-base font-bold text-indigo-700 tabular-nums">Rp {{ number_format($deposito->payoutAmount(), 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Dates card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Tanggal Mulai</div>
                    <div class="font-semibold text-gray-800">{{ $deposito->start_date->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Tanggal Jatuh Tempo</div>
                    <div class="font-semibold text-gray-800">{{ $deposito->maturity_date->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wide">Sisa Waktu</div>
                    <div class="font-semibold {{ $daysLeft > 0 ? 'text-gray-800' : 'text-amber-700' }}">
                        @if($daysLeft > 0)
                            {{ $daysLeft }} hari lagi
                        @elseif($daysLeft === 0)
                            Jatuh tempo hari ini
                        @else
                            Sudah jatuh tempo {{ abs($daysLeft) }} hari yang lalu
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Cair action --}}
        @if($canCair)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                <div class="text-sm font-bold text-amber-800 mb-2">Deposito sudah jatuh tempo — siap dicairkan</div>
                <p class="text-xs text-amber-700 mb-4">
                    Pencairan akan membuat 2 transaksi:
                    (1) Penarikan pokok Rp {{ number_format($deposito->principal_amount, 0, ',', '.') }} (kurangi liability simpanan), dan
                    (2) Beban bunga Rp {{ number_format($deposito->interestAmount(), 0, ',', '.') }} (expense kas).
                </p>
                <form method="POST" action="{{ route('admin.deposito.cair', $deposito) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Tanggal Cair</label>
                        <input type="date" name="trx_date" value="{{ now()->toDateString() }}"
                               min="{{ $deposito->maturity_date->toDateString() }}"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Kas Sumber</label>
                        <select name="cash_account_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white" required>
                            <option value="">— Pilih Kas —</option>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Kategori Beban Bunga
                        </label>
                        <select name="interest_cash_category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white" {{ $deposito->interestAmount() > 0 ? 'required' : '' }}>
                            <option value="">— Pilih Kategori EXPENSE —</option>
                            @foreach($expenseCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @if($expenseCategories->isEmpty())
                            <p class="text-xs text-red-600 mt-1">Belum ada Kategori Kas EXPENSE. Buat dulu di Master Data.</p>
                        @endif
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition"
                                onclick="return confirm('Cairkan deposito {{ $deposito->reference_code }}?')">
                            Cairkan Deposito
                        </button>
                    </div>
                </form>
            </div>
        @elseif($deposito->status === \App\Models\DepositoAccount::STATUS_ACTIVE)
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                Deposito masih aktif. Pencairan dapat dilakukan mulai
                <strong>{{ $deposito->maturity_date->format('d/m/Y') }}</strong>.
            </div>
        @elseif($deposito->status === \App\Models\DepositoAccount::STATUS_WITHDRAWN)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-600">
                Deposito ini sudah dicairkan.
            </div>
        @endif

        {{-- Linked transactions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">
                Transaksi Terkait
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">Tanggal</th>
                        <th class="text-left p-3">Tipe</th>
                        <th class="text-right p-3">Jumlah</th>
                        <th class="text-left p-3">Kas</th>
                        <th class="text-left p-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deposito->transactions as $tx)
                        <tr class="border-t {{ $tx->is_voided ? 'opacity-50 line-through' : '' }}">
                            <td class="p-3 text-xs">{{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 text-xs rounded font-semibold {{ $tx->type === 'DEPOSIT' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $tx->type === 'DEPOSIT' ? 'SETOR' : 'CAIR' }}
                                </span>
                            </td>
                            <td class="p-3 text-right tabular-nums">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                            <td class="p-3 text-xs">{{ $tx->cashAccount?->name ?? '—' }}</td>
                            <td class="p-3 text-xs text-gray-600">{{ $tx->description ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-3 text-center text-gray-400 text-xs">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
