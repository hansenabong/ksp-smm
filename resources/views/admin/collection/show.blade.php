<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.collection.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-800">← Penagihan</a>
                <span class="text-gray-300">/</span>
                <h2 class="text-lg font-semibold text-gray-800">{{ $loan->member->name }}</h2>
            </div>
            <div class="flex items-center gap-2">
                @if($loan->status === 'ACTIVE')
                @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN_COLLECTION']))
                <a href="{{ route('admin.collection.settlement', $loan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                    Pelunasan
                </a>
                @endif
                <a href="{{ route('admin.collection.schedule', $loan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg transition">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Amortisasi
                </a>
                <a href="{{ route('admin.collection.subledger.pdf', $loan) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-medium rounded-lg transition">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 4v16M6 4v16"/></svg>
                    SubLedger
                </a>
                @endif
                <a href="{{ route('admin.loans.active.show', $loan) }}"
                   class="text-xs text-indigo-600 hover:underline">Lihat Detail Pinjaman →</a>
            </div>
        </div>
    </x-slot>

    @php
        $levelLabels = \App\Models\CollectionRecord::LEVELS;
        $levelColors = \App\Models\CollectionRecord::LEVEL_COLORS;
        $currentLevel = $record->escalation_level ?? 0;
        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/'.str_pad($loan->id, 5, '0', STR_PAD_LEFT).'/'.date('Y', strtotime($loan->disbursed_date)));

        $badgeClass = function(int $level) use ($levelColors) {
            return match($levelColors[$level] ?? 'gray') {
                'yellow' => 'bg-yellow-100 text-yellow-700',
                'orange' => 'bg-orange-100 text-orange-700',
                'red'    => 'bg-red-100 text-red-700',
                'purple' => 'bg-purple-100 text-purple-700',
                'rose'   => 'bg-rose-100 text-rose-700',
                default  => 'bg-gray-100 text-gray-600',
            };
        };
    @endphp

    <div class="space-y-5">

        {{-- Loan Summary --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Ringkasan Pinjaman</div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-0 divide-x divide-y divide-gray-100">
                @php
                    $items = [
                        ['label' => 'No. Kontrak',     'value' => $noPk],
                        ['label' => 'Tgl. Cair',       'value' => \Carbon\Carbon::parse($loan->disbursed_date)->format('d/m/Y')],
                        ['label' => 'Plafon',          'value' => 'Rp '.number_format($loan->principal)],
                        ['label' => 'Tenor',           'value' => $loan->tenor_months.' bulan'],
                        ['label' => 'Angsuran/bln',    'value' => 'Rp '.number_format($loan->monthly_installment)],
                        ['label' => 'Total Dibayar',   'value' => 'Rp '.number_format($loan->total_paid)],
                        ['label' => 'Outstanding',     'value' => 'Rp '.number_format($loan->outstanding), 'bold' => true, 'color' => 'text-indigo-700'],
                        ['label' => 'Tunggakan Est.',  'value' => $arrears > 0 ? 'Rp '.number_format($arrears) : 'Tidak ada', 'bold' => true, 'color' => $arrears > 0 ? 'text-red-600' : 'text-green-600'],
                    ];
                @endphp
                @foreach($items as $item)
                <div class="px-4 py-3">
                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $item['label'] }}</div>
                    <div class="text-sm {{ ($item['bold'] ?? false) ? 'font-bold' : 'font-medium' }} {{ $item['color'] ?? 'text-gray-800' }} mt-0.5">
                        {{ $item['value'] }}
                    </div>
                </div>
                @endforeach
            </div>
            @if($monthsLate > 0)
            <div class="px-5 py-2 bg-red-50 border-t border-red-100">
                <span class="text-xs text-red-700 font-semibold">⚠ Estimasi {{ $monthsLate }} bulan dalam tunggakan</span>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Collection Record Form --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Catatan Penagihan</div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeClass($currentLevel) }}">
                            {{ $levelLabels[$currentLevel] }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.collection.update', $loan) }}" class="px-5 py-4 space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Cabang
                        </label>
                        <input type="text" name="branch" value="{{ $record->branch }}"
                            placeholder="Contoh: BEKASI, TAJUR, CIPUTAT"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Petugas Penagihan
                        </label>
                        <select name="petugas_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">— Belum ditugaskan —</option>
                            @foreach($petugasList as $pt)
                            <option value="{{ $pt->id }}" @selected($record->petugas_id == $pt->id)>{{ $pt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_restruk" value="1" class="rounded border-gray-300"
                                {{ $record->is_restruk ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 font-medium">Pinjaman Restrukturisasi</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Tgl. Kontak Terakhir
                            </label>
                            <input type="date" name="last_contact_date"
                                value="{{ $record->last_contact_date?->format('Y-m-d') }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Rencana Kontak
                            </label>
                            <input type="date" name="next_contact_date"
                                value="{{ $record->next_contact_date?->format('Y-m-d') }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        </div>
                    </div>

                    {{-- Property / Agunan fields (for Data Master DHIA) --}}
                    <details class="border border-gray-200 rounded-lg" open>
                        <summary class="px-3 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wide cursor-pointer select-none">
                            Data Agunan &amp; Alamat (DHIA)
                        </summary>
                        <div class="px-3 pb-3 pt-2 space-y-3">
                            @php
                                $njopDefault = $record->njop ?: ($dhiaDefaults['njop'] ?? null);
                                $ltDefault   = $record->luas_tanah ?: ($dhiaDefaults['luas_tanah'] ?? null);
                                $lbDefault   = $record->luas_bangunan ?: ($dhiaDefaults['luas_bangunan'] ?? null);
                                $hasFormDataLink = ($dhiaDefaults['njop'] ?? null) || ($dhiaDefaults['luas_tanah'] ?? null) || ($dhiaDefaults['luas_bangunan'] ?? null);
                            @endphp
                            @if($hasFormDataLink)
                            <p class="text-[11px] text-indigo-600 bg-indigo-50 border border-indigo-100 rounded px-2 py-1.5">
                                Nilai NJOP / LT / LB diambil otomatis dari Data Agunan pengajuan pinjaman. Anda dapat menimpa nilai tersebut di sini.
                            </p>
                            @endif
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">NJOP (Rp)</label>
                                    <input type="text" data-money name="njop" value="{{ $njopDefault }}"
                                        placeholder="0"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    @if(($dhiaDefaults['njop'] ?? null) && !$record->njop)
                                    <p class="text-[10px] text-gray-400 mt-0.5">↳ dari pengajuan pinjaman</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Luas Tanah (m²)</label>
                                    <input type="number" name="luas_tanah" value="{{ $ltDefault }}" min="0"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    @if(($dhiaDefaults['luas_tanah'] ?? null) && !$record->luas_tanah)
                                    <p class="text-[10px] text-gray-400 mt-0.5">↳ dari pengajuan pinjaman</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Luas Bangunan (m²)</label>
                                    <input type="number" name="luas_bangunan" value="{{ $lbDefault }}" min="0"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    @if(($dhiaDefaults['luas_bangunan'] ?? null) && !$record->luas_bangunan)
                                    <p class="text-[10px] text-gray-400 mt-0.5">↳ dari pengajuan pinjaman</p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Alamat Tinggal (Saat Ini)</label>
                                <textarea name="alamat_tinggal" rows="2"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                    placeholder="Jika berbeda dari alamat KTP...">{{ $record->alamat_tinggal }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Wilayah (KOTA)</label>
                                <input type="text" name="wilayah" value="{{ $record->wilayah }}"
                                    placeholder="Contoh: JAKARTA SELATAN, BEKASI, BOGOR"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            </div>
                        </div>
                    </details>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Catatan
                        </label>
                        <textarea name="notes" rows="3"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            placeholder="Catatan perkembangan penagihan...">{{ $record->notes }}</textarea>
                    </div>

                    @if(session('success'))
                    <div class="text-green-600 text-xs">{{ session('success') }}</div>
                    @endif

                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 font-medium">
                        Simpan Catatan
                    </button>
                </form>
            </div>

            {{-- Generate Letter --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Buat Surat</div>
                </div>
                <form method="POST" action="{{ route('admin.collection.letters.store', $loan) }}" target="_blank" class="px-5 py-4 space-y-4"
                    x-data="{
                        letterType: '{{ collect(\App\Models\CollectionLetter::TYPE_LEVEL)->filter(fn($l) => $l == $currentLevel + 1)->keys()->first() ?? 'SP1' }}',
                        nextNumbers: @json($nextNumbers),
                        get suggestedNumber() { return this.nextNumbers[this.letterType] ?? '' }
                    }">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Jenis Surat <span class="text-red-500">*</span>
                        </label>
                        <select name="letter_type" required x-model="letterType"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            @foreach(\App\Models\CollectionLetter::TYPES as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Eskalasi saat ini: <strong>{{ $levelLabels[$currentLevel] }}</strong></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            No. Surat <span class="text-xs text-gray-400 font-normal">(otomatis jika dikosongkan)</span>
                        </label>
                        <input type="text" name="letter_number"
                            :placeholder="suggestedNumber"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <p class="text-xs text-gray-400 mt-1">Kosongkan untuk menggunakan nomor otomatis: <span class="font-mono" x-text="suggestedNumber"></span></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Tanggal Surat <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="letter_date" required
                            value="{{ date('Y-m-d') }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Catatan <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea name="notes" rows="2"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                            placeholder="Catatan internal..."></textarea>
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Buat & Unduh PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Letter History --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Riwayat Surat ({{ $letters->count() }})</div>
            </div>
            @if($letters->isEmpty())
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada surat yang dibuat.</div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-left">Jenis</th>
                        <th class="px-4 py-2 text-left">No. Surat</th>
                        <th class="px-4 py-2 text-left">Dibuat Oleh</th>
                        <th class="px-4 py-2 text-left">Catatan</th>
                        <th class="px-4 py-2 text-center">PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($letters as $letter)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-600">{{ $letter->letter_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                {{ match($letter->letter_type) {
                                    'SP1'    => 'bg-yellow-100 text-yellow-700',
                                    'SP2'    => 'bg-orange-100 text-orange-700',
                                    'SP3'    => 'bg-red-100 text-red-700',
                                    'SOMASI' => 'bg-purple-100 text-purple-700',
                                    'CESSIE' => 'bg-rose-100 text-rose-700',
                                    default  => 'bg-gray-100 text-gray-600',
                                } }}">
                                {{ $letter->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $letter->letter_number }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $letter->creator?->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-400">{{ $letter->notes ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('admin.collection.letters.pdf', $letter) }}" target="_blank"
                               class="inline-flex items-center px-2.5 py-1 text-xs bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 font-medium">
                                PDF
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Recent Payments --}}
        @if($loan->payments->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide">Riwayat Angsuran Terakhir</div>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-right">Jumlah</th>
                        <th class="px-4 py-2 text-right">Denda</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($loan->payments->take(10) as $pay)
                    <tr class="{{ $pay->is_voided ? 'opacity-50' : 'hover:bg-gray-50' }}">
                        <td class="px-4 py-2 text-gray-600">{{ \Carbon\Carbon::parse($pay->trx_date)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-right font-mono text-gray-700">{{ number_format($pay->amount) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-xs {{ $pay->penalty_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $pay->penalty_amount > 0 ? number_format($pay->penalty_amount) : '-' }}
                        </td>
                        <td class="px-4 py-2">
                            @if($pay->is_voided)
                            <span class="text-xs text-gray-400 line-through">Dibatalkan</span>
                            @else
                            <span class="text-xs text-green-600">Sah</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</x-admin-layout>
