<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Aktif</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pinjaman #{{ $loan->id }}</h2>
            </div>
            <div class="flex gap-2">
                @if($loan->status === 'ACTIVE')
                <a href="{{ route('admin.loans.payments.create', $loan) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                    Bayar Angsuran
                </a>
                @endif
                @if($loan->loan_application_id)
                <a href="{{ route('admin.loans.applications.agreement', $loan->loan_application_id) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Perjanjian
                </a>
                @endif
                <a href="{{ route('admin.loans.active.index') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">

            @if (session('success'))
                <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- ===== SUMMARY CARD (always visible) ===== --}}
            @php
                $statusConfig = match($loan->status) {
                    'ACTIVE' => ['bg' => 'bg-green-50 text-green-700 ring-green-200',   'dot' => 'bg-green-400'],
                    'CLOSED' => ['bg' => 'bg-gray-100 text-gray-600 ring-gray-300',     'dot' => 'bg-gray-400'],
                    default  => ['bg' => 'bg-yellow-50 text-yellow-700 ring-yellow-200','dot' => 'bg-yellow-400'],
                };
                $pctPaid = $loan->total_payable > 0
                    ? min(100, round(($loan->total_paid / $loan->total_payable) * 100))
                    : 0;
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold ring-1 ring-inset {{ $statusConfig['bg'] }}">
                            <span class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                            {{ $loan->status }}
                        </span>
                        <span class="text-gray-400 text-sm">{{ $loan->loanProduct?->name ?? '-' }}</span>
                    </div>
                    <div class="text-sm text-gray-500">Cair: {{ $loan->disbursed_date?->format('d M Y') }}</div>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pemohon</div>
                        <div class="text-lg font-bold text-gray-800">{{ $loan->member?->name ?? '-' }}</div>
                        <div class="text-sm text-gray-500">No. Anggota: {{ $loan->member?->member_no ?? $loan->member_id }}</div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Pokok Pinjaman</div>
                            <div class="font-bold text-gray-800 text-sm">Rp {{ number_format($loan->principal, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $loan->tenor_months }} bulan</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Angsuran/bln</div>
                            <div class="font-bold text-gray-800 text-sm">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $loan->interest_monthly_percent }}%/bln</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Sudah Dibayar</div>
                            <div class="font-bold text-gray-800 text-sm">Rp {{ number_format($loan->total_paid, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">dari Rp {{ number_format($loan->total_payable, 0, ',', '.') }}</div>
                        </div>
                        <div class="{{ $loan->outstanding > 0 ? 'bg-indigo-50' : 'bg-green-50' }} rounded-lg p-3">
                            <div class="text-xs {{ $loan->outstanding > 0 ? 'text-indigo-400' : 'text-green-400' }} mb-1">Sisa Tagihan</div>
                            <div class="font-bold {{ $loan->outstanding > 0 ? 'text-indigo-800' : 'text-green-700' }} text-sm">
                                Rp {{ number_format($loan->outstanding, 0, ',', '.') }}
                            </div>
                            <div class="text-xs {{ $loan->outstanding > 0 ? 'text-indigo-300' : 'text-green-400' }} mt-0.5">{{ $pctPaid }}% lunas</div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $pctPaid }}%"></div>
                    </div>
                </div>
            </div>

            {{-- ===== TABS ===== --}}
            <div x-data="{ tab: 'pembayaran' }">

                {{-- Tab bar --}}
                <div class="flex bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    @php
                        $fd      = $loan->application?->form_data ?? [];
                        $hasForm = !empty(array_filter($fd));
                        $hasDocs = $loan->application?->files->isNotEmpty();
                    @endphp

                    <button @click="tab = 'pembayaran'"
                        :class="tab === 'pembayaran' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="flex-1 py-3 px-4 text-sm font-medium transition text-center">
                        Riwayat Pembayaran
                        <span class="ml-1 text-xs rounded-full px-1.5 py-0.5 bg-gray-100 text-gray-500">{{ $loan->payments->count() }}</span>
                    </button>

                    <button @click="tab = 'ringkasan'"
                        :class="tab === 'ringkasan' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="flex-1 py-3 px-4 text-sm font-medium transition text-center border-l border-gray-200">
                        Rincian Pinjaman
                    </button>

                    <button @click="tab = 'pemohon'"
                        :class="tab === 'pemohon' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="flex-1 py-3 px-4 text-sm font-medium transition text-center border-l border-gray-200 relative">
                        Data Pemohon
                        @if(!$hasForm)
                            <span class="ml-1 text-xs text-gray-300">–</span>
                        @endif
                    </button>

                    <button @click="tab = 'dokumen'"
                        :class="tab === 'dokumen' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-indigo-50/60' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        class="flex-1 py-3 px-4 text-sm font-medium transition text-center border-l border-gray-200">
                        Dokumen
                        @if($hasDocs)
                            <span class="ml-1 text-xs rounded-full px-1.5 py-0.5 bg-gray-100 text-gray-500">{{ $loan->application->files->count() }}</span>
                        @endif
                    </button>
                </div>

                {{-- ===== TAB: RIWAYAT PEMBAYARAN ===== --}}
                <div x-show="tab === 'pembayaran'" x-cloak>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-1">
                        <div class="px-6 py-3.5 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Riwayat Pembayaran</h3>
                            @if($loan->status === 'ACTIVE')
                            <a href="{{ route('admin.loans.payments.create', $loan) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                + Bayar Angsuran
                            </a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jumlah</th>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kas</th>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan</th>
                                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dicatat oleh</th>
                                        <th class="px-5 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($loan->payments as $p)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-5 py-3.5">{{ $p->trx_date }}</td>
                                            <td class="px-5 py-3.5 text-right font-medium text-gray-800">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-gray-600">{{ $p->cashAccount?->name ?? '-' }}</td>
                                            <td class="px-5 py-3.5 text-gray-500">{{ $p->note ?? '-' }}</td>
                                            <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $p->creator?->name ?? $p->creator?->email ?? '-' }}</td>
                                            <td class="px-5 py-3.5 text-right">
                                                @if ($p->is_voided)
                                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-md bg-red-100 text-red-700 font-medium">VOID</span>
                                                @else
                                                    <a href="{{ route('admin.loans.payments.void.create', $p) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition">
                                                        VOID
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada pembayaran.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: RINCIAN PINJAMAN ===== --}}
                <div x-show="tab === 'ringkasan'" x-cloak>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-1">
                        <div class="px-6 py-3.5 bg-gray-50 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700">Rincian Komponen Pinjaman</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-5 text-sm">
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Produk Pinjaman</div>
                                <div class="font-semibold text-gray-800">{{ $loan->loanProduct?->name ?? '-' }}</div>
                                @if($loan->loanProduct?->method)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $loan->loanProduct->method }}</div>
                                @endif
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Kas Pencairan</div>
                                <div class="font-semibold text-gray-800">{{ $loan->disbursedCashAccount?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Dibuat oleh</div>
                                <div class="font-semibold text-gray-800">{{ $loan->creator?->name ?? $loan->creator?->email ?? '-' }}</div>
                            </div>

                            <div class="md:col-span-3 border-t border-gray-100 pt-5">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Komponen Angsuran per Bulan</div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="text-xs text-gray-400 mb-1">Pokok / bln</div>
                                        <div class="font-bold text-gray-800">Rp {{ number_format($loan->principal_monthly, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <div class="text-xs text-blue-400 mb-1">Bunga / bln</div>
                                        <div class="font-bold text-blue-800">Rp {{ number_format($loan->interest_monthly, 0, ',', '.') }}</div>
                                        <div class="text-xs text-blue-300 mt-0.5">{{ $loan->interest_monthly_percent }}%</div>
                                    </div>
                                    <div class="bg-amber-50 rounded-lg p-4">
                                        <div class="text-xs text-amber-400 mb-1">Admin / bln</div>
                                        <div class="font-bold text-amber-800">Rp {{ number_format($loan->admin_monthly ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <div class="text-xs text-purple-400 mb-1">Asuransi / bln</div>
                                        <div class="font-bold text-purple-800">Rp {{ number_format($loan->insurance_monthly ?? 0, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-3 border-t border-gray-100 pt-5">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Rekapitulasi</div>
                                <div class="space-y-2 max-w-xs">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Total Tagihan</span>
                                        <span class="font-medium text-gray-800">Rp {{ number_format($loan->total_payable, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Sudah Dibayar</span>
                                        <span class="font-medium text-green-700">Rp {{ number_format($loan->total_paid, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm font-semibold border-t border-gray-100 pt-2">
                                        <span class="text-gray-700">Sisa Tagihan</span>
                                        <span class="text-indigo-700">Rp {{ number_format($loan->outstanding, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($loan->application)
                            <div class="md:col-span-3 border-t border-gray-100 pt-5 text-xs text-gray-400">
                                Pengajuan: <a href="{{ route('admin.loans.applications.show', $loan->application) }}"
                                    class="text-indigo-500 hover:underline">#{{ $loan->application->id }}</a>
                                &middot; Diajukan {{ $loan->application->trx_date?->format('d M Y') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ===== TAB: DATA PEMOHON ===== --}}
                <div x-show="tab === 'pemohon'" x-cloak>
                    @if(!$hasForm)
                        <div class="mt-1 bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-400 text-sm">
                            Data formulir tidak tersedia untuk pinjaman ini.
                        </div>
                    @else
                    <div class="space-y-4 mt-1">

                        {{-- Data Pribadi --}}
                        @if(!empty($fd['nama_lengkap'] ?? $fd['no_ktp'] ?? $fd['tgl_lahir'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-blue-50 border-b border-blue-100">
                                <h3 class="text-sm font-semibold text-blue-800">Data Pribadi Pemohon</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Nama Lengkap</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['nama_lengkap'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jenis Kelamin</div>
                                    <div class="font-semibold text-gray-800">{{ ($fd['kelamin'] ?? '') === 'L' ? 'Laki-laki' : (($fd['kelamin'] ?? '') === 'P' ? 'Perempuan' : '-') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">No. KTP</div>
                                    <div class="font-semibold text-gray-800 font-mono tracking-wide">{{ $fd['no_ktp'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Tanggal Lahir</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['tgl_lahir'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">KTP Berlaku s/d</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['ktp_berlaku'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Status Perkawinan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['status_perkawinan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Pendidikan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['pendidikan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Nama Ibu Kandung</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['nama_ibu_kandung'] ?? '-' }}</div>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="text-xs text-gray-400 mb-0.5">Alamat</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['alamat'] ?? '-' }}, RT {{ $fd['rt'] ?? '-' }}/RW {{ $fd['rw'] ?? '-' }}, {{ $fd['kota'] ?? '-' }} {{ $fd['kode_pos'] ?? '' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Telp. Rumah</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['telp_rumah'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">No. HP</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['hp'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Kepemilikan Rumah</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['kepemilikan_rumah'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Data Pasangan --}}
                        @if(!empty($fd['nama_pasangan'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-pink-50 border-b border-pink-100">
                                <h3 class="text-sm font-semibold text-pink-800">Data Istri / Suami</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Nama</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['nama_pasangan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jumlah Tanggungan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['jumlah_tanggungan'] ?? '-' }} orang</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">No. KTP</div>
                                    <div class="font-semibold text-gray-800 font-mono">{{ $fd['no_ktp_pasangan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Tanggal Lahir</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['tgl_lahir_pasangan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">KTP Berlaku s/d</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['ktp_berlaku_pasangan'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Kontak Darurat --}}
                        @if(!empty($fd['nama_darurat'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-orange-50 border-b border-orange-100">
                                <h3 class="text-sm font-semibold text-orange-800">Kontak Darurat</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Nama</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['nama_darurat'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Hubungan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['hubungan_darurat'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Telp / HP</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['telp_darurat'] ?? '-' }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Alamat</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['alamat_darurat'] ?? '-' }}, RT {{ $fd['rt_darurat'] ?? '-' }}/RW {{ $fd['rw_darurat'] ?? '-' }}, {{ $fd['kelurahan_darurat'] ?? '' }}, {{ $fd['kecamatan_darurat'] ?? '' }}, {{ $fd['kota_darurat'] ?? '-' }} {{ $fd['kode_pos_darurat'] ?? '' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Pekerjaan Pemohon --}}
                        @if(!empty($fd['pekerjaan'] ?? $fd['perusahaan'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-teal-50 border-b border-teal-100">
                                <h3 class="text-sm font-semibold text-teal-800">Data Pekerjaan Pemohon</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Pekerjaan / Profesi</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['pekerjaan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Nama Perusahaan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['perusahaan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jabatan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['jabatan'] ?? '-' }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Alamat Perusahaan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['alamat_perusahaan'] ?? '-' }}, {{ $fd['kota_perusahaan'] ?? '' }} {{ $fd['kode_pos_perusahaan'] ?? '' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Telp.</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['telp_perusahaan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jenis Usaha</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['jenis_usaha'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Masa Kerja</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['masa_kerja'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Pekerjaan Pasangan --}}
                        @if(!empty($fd['pekerjaan_pasangan'] ?? $fd['perusahaan_pasangan'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-teal-50 border-b border-teal-100">
                                <h3 class="text-sm font-semibold text-teal-800">Data Pekerjaan Istri / Suami</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Pekerjaan / Profesi</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['pekerjaan_pasangan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Nama Perusahaan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['perusahaan_pasangan'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jabatan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['jabatan_pasangan'] ?? '-' }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-xs text-gray-400 mb-0.5">Alamat Perusahaan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['alamat_perusahaan_pasangan'] ?? '-' }}, {{ $fd['kota_perusahaan_pasangan'] ?? '' }} {{ $fd['kode_pos_perusahaan_pasangan'] ?? '' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Masa Kerja</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['masa_kerja_pasangan'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Agunan --}}
                        @if(!empty($fd['lokasi_agunan'] ?? $fd['jenis_kendaraan'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-amber-50 border-b border-amber-100">
                                <h3 class="text-sm font-semibold text-amber-800">Data Agunan / Jaminan</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                @if(!empty($fd['lokasi_agunan'] ?? null))
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tanah &amp; Bangunan</div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                        <div class="md:col-span-3">
                                            <div class="text-xs text-gray-400 mb-0.5">Lokasi</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['lokasi_agunan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Status</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['status_agunan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Atas Nama</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['atas_nama_agunan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Nilai NJOP</div>
                                            <div class="font-semibold text-gray-800">Rp {{ isset($fd['nilai_agunan']) ? number_format($fd['nilai_agunan'], 0, ',', '.') : '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">No. Hak</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['no_hak'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Luas Tanah</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['luas_tanah'] ?? '-' }} m²</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Luas Bangunan</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['luas_bangunan'] ?? '-' }} m²</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">No. IMB</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['no_imb'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if(!empty($fd['jenis_kendaraan'] ?? null))
                                <div class="{{ !empty($fd['lokasi_agunan'] ?? null) ? 'border-t border-gray-100 pt-5' : '' }}">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Kendaraan &amp; BPKB</div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Jenis Kendaraan</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['jenis_kendaraan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Tahun</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['tahun_kendaraan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Warna</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['warna_kendaraan'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">No. BPKB</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['no_bpkb'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">Atas Nama</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['atas_nama_bpkb'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">No. Mesin</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['no_mesin'] ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-400 mb-0.5">No. Rangka</div>
                                            <div class="font-semibold text-gray-800">{{ $fd['no_rangka'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Pendapatan & Pengeluaran --}}
                        @if(!empty($fd['penghasilan_pemohon'] ?? null))
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-3.5 bg-green-50 border-b border-green-100">
                                <h3 class="text-sm font-semibold text-green-800">Pendapatan &amp; Pengeluaran per Bulan</h3>
                            </div>
                            <div class="p-6">
                                @php
                                    $totalPendapatan  = ($fd['penghasilan_pemohon'] ?? 0) + ($fd['penghasilan_pasangan'] ?? 0) + ($fd['penghasilan_lain'] ?? 0);
                                    $totalPengeluaran = ($fd['biaya_sewa'] ?? 0) + ($fd['biaya_hidup'] ?? 0) + ($fd['angsuran_lain'] ?? 0) + ($fd['biaya_lain'] ?? 0);
                                    $sisa             = $totalPendapatan - $totalPengeluaran;
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Pendapatan (A)</div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Penghasilan Pemohon</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['penghasilan_pemohon'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Penghasilan Istri/Suami</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['penghasilan_pasangan'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Penghasilan Lain-lain</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['penghasilan_lain'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm font-semibold border-t border-gray-100 pt-2"><span class="text-gray-700">Total Pendapatan</span><span class="text-gray-800">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Pengeluaran (B)</div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Biaya Sewa</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['biaya_sewa'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Biaya Hidup</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['biaya_hidup'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Angsuran Pinjaman Lain</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['angsuran_lain'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm"><span class="text-gray-500">Biaya Lain-lain</span><span class="font-medium text-gray-800">Rp {{ number_format($fd['biaya_lain'] ?? 0, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-sm font-semibold border-t border-gray-100 pt-2"><span class="text-gray-700">Total Pengeluaran</span><span class="text-gray-800">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-5 -mx-6 -mb-6 px-6 py-4 flex items-center justify-between {{ $sisa >= 0 ? 'bg-green-50 border-t border-green-100' : 'bg-red-50 border-t border-red-100' }}">
                                    <span class="font-semibold text-sm {{ $sisa >= 0 ? 'text-green-800' : 'text-red-800' }}">Sisa Pendapatan per Bulan (A &minus; B)</span>
                                    <span class="text-xl font-bold {{ $sisa >= 0 ? 'text-green-700' : 'text-red-700' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>
                    @endif
                </div>

                {{-- ===== TAB: DOKUMEN ===== --}}
                <div x-show="tab === 'dokumen'" x-cloak>
                    @if(!$hasDocs)
                        <div class="mt-1 bg-white rounded-xl border border-gray-200 shadow-sm p-10 text-center text-gray-400 text-sm">
                            Tidak ada dokumen yang diunggah.
                        </div>
                    @else
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mt-1">
                        <div class="px-6 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-700">Dokumen Pendukung</h3>
                            <span class="text-xs text-gray-400">{{ $loan->application->files->count() }} file</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50">
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama File</th>
                                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ukuran</th>
                                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Diunggah oleh</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($loan->application->files as $file)
                                    @php
                                        $catColor = match($file->category) {
                                            'KTP'    => 'bg-blue-100 text-blue-700',
                                            'KK'     => 'bg-purple-100 text-purple-700',
                                            'AGUNAN' => 'bg-amber-100 text-amber-700',
                                            default  => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $catColor }}">
                                                {{ $file->category }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-sm text-gray-700">{{ $file->original_name }}</td>
                                        <td class="px-6 py-3.5 text-sm text-right text-gray-500">{{ number_format($file->size / 1024, 1) }} KB</td>
                                        <td class="px-6 py-3.5 text-sm text-gray-500">{{ $file->uploader?->name ?? '-' }}</td>
                                        <td class="px-6 py-3.5 text-right">
                                            <a href="{{ route('admin.loans.applications.files.download', [$loan->application, $file]) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                                &darr; Unduh
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

            </div>{{-- end x-data tabs --}}

    </div>
</x-admin-layout>
