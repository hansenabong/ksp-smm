<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="shrink-0">
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Pengajuan</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pengajuan #{{ $application->id }}</h2>
            </div>
            <div class="flex items-center gap-2 flex-wrap justify-end">
                @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN_DATA','TREASURY','PENGAWAS']))
                <a href="{{ route('admin.loans.applications.review_pdf', $application) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition"
                   title="Cetak Lembar Review Pengajuan (PDF internal)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Review
                </a>
                @endif
                @if($application->status === 'APPROVED')
                    @php
                        $fdShow = $application->form_data ?? [];
                        $showPkA = !empty($fdShow['no_hak'] ?? $fdShow['lokasi_agunan'] ?? $fdShow['kota_agunan'] ?? null);
                        $showPkB = !empty($fdShow['jenis_kendaraan'] ?? null);
                        $linkedLoan = \App\Models\Loan::where('loan_application_id', $application->id)->first();
                    @endphp
                    @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','TREASURY','LEGAL','PENGAWAS']))
                    <a href="{{ route('admin.loans.applications.penawaran', $application) }}?type=sertipikat"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak OL — Penawaran Pembiayaan Sertipikat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        OL Sertipikat
                    </a>
                    <a href="{{ route('admin.loans.applications.penawaran', $application) }}?type=kendaraan"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak OL — Penawaran Pembiayaan Kendaraan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        OL Kendaraan
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement', $application) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 {{ $showPkA ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-500 hover:bg-gray-600' }} text-white text-sm font-medium rounded-lg transition"
                       title="Cetak PK A — Perjanjian Pembiayaan dengan Jaminan Sertipikat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PK A (Sertipikat)
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement.vehicle', $application) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 {{ $showPkB ? 'bg-teal-600 hover:bg-teal-700' : 'bg-gray-500 hover:bg-gray-600' }} text-white text-sm font-medium rounded-lg transition"
                       title="Cetak PK B — Perjanjian Pembiayaan dengan Jaminan BPKB Kendaraan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PK B (Kendaraan)
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement.word', $application) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
                       title="Download PK A sebagai Word (.docx)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        PK A Word
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement.vehicle.word', $application) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition"
                       title="Download PK B sebagai Word (.docx)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        PK B Word
                    </a>
                    @if($linkedLoan)
                    <div class="w-px h-6 bg-gray-300"></div>
                    <a href="{{ route('admin.loans.active.permohonan.dana', $linkedLoan) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak Dokumen Permohonan Dana">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Permohonan Dana
                    </a>
                    <a href="{{ route('admin.loans.active.kwitansi.plafon', $linkedLoan) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak Kwitansi Plafon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Kwitansi Plafon
                    </a>
                    <a href="{{ route('admin.loans.active.kwitansi.pencairan', $linkedLoan) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak Kwitansi Pencairan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Kwitansi Pencairan
                    </a>
                    <a href="{{ route('admin.loans.active.schedule', $linkedLoan) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition"
                       title="Cetak Jadwal Angsuran">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Jadwal Angsuran
                    </a>
                    @endif
                    @endif
                    @if(auth()->user()->hasRole('SUPER_ADMIN'))
                        <a href="{{ route('admin.loans.applications.edit_personal', $application) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Edit Data Pribadi
                        </a>
                    @endif
                @endif
                @if($application->status === 'PENDING' && auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN_DATA']))
                    <a href="{{ route('admin.loans.applications.edit', $application) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Pengajuan
                    </a>
                @endif
                <a href="{{ route('admin.loans.applications.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="flex items-center gap-2 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    <div class="font-semibold mb-1">Ada kesalahan:</div>
                    <ul class="list-disc ml-5 space-y-0.5">
                        @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ===== HERO / SUMMARY CARD ===== --}}
            @php
                $statusConfig = match($application->status) {
                    'PENDING'           => ['bg' => 'bg-yellow-50 text-yellow-700 ring-yellow-200', 'dot' => 'bg-yellow-400'],
                    'FINANCE_REVIEWED'  => ['bg' => 'bg-blue-50 text-blue-700 ring-blue-200',       'dot' => 'bg-blue-400'],
                    'APPROVED'          => ['bg' => 'bg-green-50 text-green-700 ring-green-200',    'dot' => 'bg-green-400'],
                    'REJECTED'          => ['bg' => 'bg-red-50 text-red-700 ring-red-200',          'dot' => 'bg-red-400'],
                    default             => ['bg' => 'bg-gray-50 text-gray-600 ring-gray-200',       'dot' => 'bg-gray-400'],
                };
                $monthly_interest = $application->principal * ($application->interest_monthly_percent / 100);
                $monthly_pokok    = $application->tenor_months > 0 ? $application->principal / $application->tenor_months : 0;
                $est_angsuran     = $monthly_pokok + $monthly_interest + $application->insurance_monthly;
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Status bar --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold ring-1 ring-inset {{ $statusConfig['bg'] }}">
                            <span class="w-2 h-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                            {{ $application->status }}
                        </span>
                        <span class="text-gray-400 text-sm">Pengajuan #{{ $application->id }}</span>
                    </div>
                    <div class="text-sm text-gray-500">{{ $application->trx_date->format('d F Y') }}</div>
                </div>

                <div class="p-6">
                    {{-- Member name --}}
                    <div class="mb-5">
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Anggota</div>
                        <div class="text-xl font-bold text-gray-800">{{ $application->member?->name ?? '-' }}</div>
                        <div class="text-sm text-gray-500">No. Anggota: {{ $application->member?->member_no ?? $application->member_id }}</div>
                    </div>

                    {{-- Key metrics --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Produk Pinjaman</div>
                            <div class="font-semibold text-gray-800 text-sm leading-snug">{{ $application->loanProduct?->name ?? '-' }}</div>
                            @if($application->method)
                                <div class="text-xs text-gray-400 mt-0.5">{{ $application->method }}</div>
                            @endif
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Pokok Pinjaman</div>
                            <div class="font-bold text-gray-800 text-sm">Rp {{ number_format($application->principal, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $application->tenor_months }} bulan</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-400 mb-1">Bunga {{ $application->method ?? 'FLAT' }}</div>
                            <div class="font-bold text-gray-800 text-sm">{{ $application->interest_monthly_percent }}%<span class="text-xs font-normal text-gray-400">/bln</span></div>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-3">
                            <div class="text-xs text-indigo-400 mb-1">Est. Angsuran/bln</div>
                            <div class="font-bold text-indigo-800 text-sm">Rp {{ number_format($est_angsuran, 0, ',', '.') }}</div>
                            @if($application->insurance_monthly)
                                <div class="text-xs text-indigo-300 mt-0.5">+ Asuransi Rp {{ number_format($application->insurance_monthly, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Meta row --}}
                    <div class="text-sm border-t border-gray-100 pt-4 grid grid-cols-1 md:grid-cols-3 gap-y-2 gap-x-4">
                        <div>
                            <span class="text-gray-400">Dibuat oleh:</span>
                            <span class="ml-1 font-medium text-gray-700">{{ $application->creator?->name ?? '-' }}</span>
                        </div>
                        @if($application->approver)
                        <div>
                            <span class="text-gray-400">{{ $application->status === 'REJECTED' ? 'Ditolak' : 'Disetujui' }} oleh:</span>
                            <span class="ml-1 font-medium text-gray-700">{{ $application->approver->name }}</span>
                            @if($application->approved_at)
                                <span class="text-gray-400 text-xs">({{ \Carbon\Carbon::parse($application->approved_at)->format('d/m/Y') }})</span>
                            @endif
                        </div>
                        @endif
                        @if(!empty(($application->form_data ?? [])['tujuan_pembiayaan'] ?? null))
                        <div>
                            <span class="text-gray-400">Tujuan Pembiayaan:</span>
                            <span class="ml-1 text-gray-700">{{ $application->form_data['tujuan_pembiayaan'] }}</span>
                        </div>
                        @endif
                        @if($application->notes)
                        <div>
                            <span class="text-gray-400">Catatan:</span>
                            <span class="ml-1 text-gray-700">{{ $application->notes }}</span>
                        </div>
                        @endif
                        @if($application->approval_note)
                        <div class="md:col-span-3">
                            <span class="text-gray-400">Catatan Approval:</span>
                            <span class="ml-1 font-medium {{ $application->status === 'REJECTED' ? 'text-red-700' : 'text-gray-700' }}">{{ $application->approval_note }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== NOTARIS ===== --}}
            @if($application->notaris_name || $application->notaris_deed_number || $application->notaris_deed_date || $application->notaris_city)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-indigo-50 border-b border-indigo-100">
                    <h3 class="text-sm font-semibold text-indigo-800">Informasi Notaris</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Nama Notaris</div>
                        <div class="font-semibold text-gray-800">{{ $application->notaris_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">No. Akta</div>
                        <div class="font-semibold text-gray-800">{{ $application->notaris_deed_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Tanggal Akta</div>
                        <div class="font-semibold text-gray-800">{{ $application->notaris_deed_date?->format('d F Y') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 mb-0.5">Kota / Kabupaten</div>
                        <div class="font-semibold text-gray-800">{{ $application->notaris_city ?? '-' }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ===== FORM DATA SECTIONS (sensitive — policy-gated) ===== --}}
            @can('viewSensitiveData', $application)
            @php $fd = $application->form_data ?? []; @endphp
            @if(!empty(array_filter($fd)))

            {{-- Data Pribadi --}}
            @if(!empty($fd['nama_lengkap'] ?? $fd['no_ktp'] ?? $fd['tgl_lahir'] ?? null))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-blue-50 border-b border-blue-100">
                    <h3 class="text-sm font-semibold text-blue-800">Data Pribadi</h3>
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
                        <div class="font-semibold text-gray-800">
                            @if(!empty($fd['ktp_seumur_hidup']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold ring-1 ring-blue-200">e-KTP · Seumur Hidup</span>
                            @else
                                {{ $fd['ktp_berlaku'] ?? '-' }}
                            @endif
                        </div>
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
                        <div class="font-semibold text-gray-800">
                            @if(!empty($fd['ktp_seumur_hidup_pasangan']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold ring-1 ring-blue-200">e-KTP · Seumur Hidup</span>
                            @else
                                {{ $fd['ktp_berlaku_pasangan'] ?? '-' }}
                            @endif
                        </div>
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

            {{-- Data Agunan --}}
            @if(!empty($fd['lokasi_agunan'] ?? $fd['jenis_kendaraan'] ?? null))
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-amber-50 border-b border-amber-100">
                    <h3 class="text-sm font-semibold text-amber-800">Data Agunan / Jaminan</h3>
                </div>
                <div class="p-6 space-y-5">
                    @if(!empty($fd['lokasi_agunan'] ?? $fd['no_hak'] ?? null))
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tanah &amp; Bangunan</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                            @if(!empty($fd['lokasi_agunan'] ?? null))
                            <div class="md:col-span-3">
                                <div class="text-xs text-gray-400 mb-0.5">Lokasi (Umum)</div>
                                <div class="font-semibold text-gray-800">{{ $fd['lokasi_agunan'] }}</div>
                            </div>
                            @endif
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Status Hak</div>
                                <div class="font-semibold text-gray-800">{{ $fd['status_agunan'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">No. Hak (SHM/SHGB)</div>
                                <div class="font-semibold text-gray-800">{{ $fd['no_hak'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Atas Nama</div>
                                <div class="font-semibold text-gray-800">{{ $fd['atas_nama_agunan'] ?? '-' }}</div>
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
                                <div class="text-xs text-gray-400 mb-0.5">Nilai NJOP</div>
                                <div class="font-semibold text-gray-800">Rp {{ isset($fd['nilai_agunan']) ? number_format($fd['nilai_agunan'], 0, ',', '.') : '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">No. IMB</div>
                                <div class="font-semibold text-gray-800">{{ $fd['no_imb'] ?? '-' }}</div>
                            </div>
                        </div>

                        @php
                            $hasPropertyAddr = !empty($fd['kelurahan_agunan'] ?? $fd['jalan_agunan'] ?? $fd['kota_agunan'] ?? null);
                        @endphp
                        @if($hasPropertyAddr)
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Alamat Objek (Pasal 3 PK)</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                                @if(!empty($fd['komplek_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Komplek</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['komplek_agunan'] }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['jalan_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Jalan</div>
                                    <div class="font-semibold text-gray-800">Jl. {{ $fd['jalan_agunan'] }}{{ !empty($fd['blok_agunan']) ? ' Blok '.$fd['blok_agunan'] : '' }}{{ !empty($fd['no_agunan']) ? ' No. '.$fd['no_agunan'] : '' }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['rt_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">RT / RW</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['rt_agunan'] ?? '-' }} / {{ $fd['rw_agunan'] ?? '-' }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['kelurahan_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Kelurahan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['kelurahan_agunan'] }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['kecamatan_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Kecamatan</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['kecamatan_agunan'] }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['kota_agunan'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Kab. / Kota</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['kota_agunan'] }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['penerbit_sertifikat'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Diterbitkan Oleh</div>
                                    <div class="font-semibold text-gray-800">{{ $fd['penerbit_sertifikat'] }}</div>
                                </div>
                                @endif
                                @if(!empty($fd['tgl_sertifikat'] ?? null))
                                <div>
                                    <div class="text-xs text-gray-400 mb-0.5">Tanggal Sertifikat</div>
                                    <div class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($fd['tgl_sertifikat'])->format('d/m/Y') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
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
                            @if(!empty($fd['merk_kendaraan'] ?? null))
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Merk / Type</div>
                                <div class="font-semibold text-gray-800">{{ trim(($fd['merk_kendaraan'] ?? '') . ' ' . ($fd['type_kendaraan'] ?? '')) ?: '-' }}</div>
                            </div>
                            @endif
                            @if(!empty($fd['model_kendaraan'] ?? null))
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Model</div>
                                <div class="font-semibold text-gray-800">{{ $fd['model_kendaraan'] }}</div>
                            </div>
                            @endif
                            @if(!empty($fd['bahan_bakar_kendaraan'] ?? null))
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">Bahan Bakar</div>
                                <div class="font-semibold text-gray-800">{{ $fd['bahan_bakar_kendaraan'] }}</div>
                            </div>
                            @endif
                            @if(!empty($fd['no_polisi'] ?? null))
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">No. Polisi</div>
                                <div class="font-semibold text-gray-800">{{ $fd['no_polisi'] }}</div>
                            </div>
                            @endif
                            @if(!empty($fd['no_faktur_kendaraan'] ?? null))
                            <div>
                                <div class="text-xs text-gray-400 mb-0.5">No. Faktur</div>
                                <div class="font-semibold text-gray-800">{{ $fd['no_faktur_kendaraan'] }}</div>
                            </div>
                            @endif
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

            @endif {{-- end array_filter --}}
            @else
            {{-- BENDAHARA or restricted role: show notice --}}
            <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-6 py-4 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <div class="font-semibold">Data Pribadi Tidak Tersedia</div>
                    <div class="text-amber-700 mt-0.5">Peran Anda tidak memiliki akses untuk melihat data pribadi, KTP, agunan, dan informasi sensitif pemohon.</div>
                </div>
            </div>
            @endcan

            @can('viewSensitiveData', $application)
            {{-- ===== DOKUMEN ===== --}}
            @if($application->files->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">Dokumen Pendukung</h3>
                    <span class="text-xs text-gray-400">{{ $application->files->count() }} file</span>
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
                            @foreach($application->files as $file)
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
                                    <a href="{{ route('admin.loans.applications.files.download', [$application, $file]) }}"
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
            @endcan

            {{-- ===== FINANCE REVIEW (FINANCE sets interest & method) ===== --}}
            @if ($application->status === 'PENDING' && auth()->user()->hasAnyRole(['SUPER_ADMIN','FINANCE']))
            <div class="bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">
                <div class="px-6 py-3.5 bg-blue-50 border-b border-blue-100">
                    <h3 class="text-sm font-semibold text-blue-800">Review Finance &mdash; Tentukan Bunga &amp; Metode</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.loans.applications.finance-review', $application) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Metode Bunga</label>
                                <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="FLAT" @selected(($application->method ?? 'FLAT') === 'FLAT')>FLAT (tetap)</option>
                                    <option value="EFEKTIF" disabled>EFEKTIF (belum tersedia)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Bunga / bulan (%)</label>
                                <input type="number" step="0.01" name="interest_monthly_percent"
                                    value="{{ old('interest_monthly_percent', $application->interest_monthly_percent) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="0">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan Finance</label>
                                <input type="text" name="finance_note" value="{{ old('finance_note') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="255">
                            </div>
                        </div>
                        <button type="submit"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            Submit Review Finance
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- ===== APPROVE / REJECT (SUPER_ADMIN only, after Finance review) ===== --}}
            @can('approve', $application)
            @if (in_array($application->status, ['PENDING', 'FINANCE_REVIEWED']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-3.5 bg-green-50 border-b border-green-100">
                        <h3 class="text-sm font-semibold text-green-800">Setujui &amp; Cairkan</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.loans.applications.approve', $application) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Pencairan</label>
                                <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                @error('trx_date')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Ambil Dari Kas</label>
                                <select name="from_cash_account_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="">— Pilih Akun Kas —</option>
                                    @foreach ($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('from_cash_account_id') == $acc->id)>
                                            {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_cash_account_id')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan (opsional)</label>
                                <input type="text" name="approval_note" value="{{ old('approval_note') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" maxlength="255">
                                @error('approval_note')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit"
                                class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                                Proses Persetujuan
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-red-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-3.5 bg-red-50 border-b border-red-100">
                        <h3 class="text-sm font-semibold text-red-800">Tolak Pengajuan</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.loans.applications.reject', $application) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1.5">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <input type="text" name="approval_note" value="{{ old('approval_note') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    maxlength="255" required placeholder="Wajib diisi...">
                                @error('approval_note')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>
                            <p class="text-xs text-gray-400">Tindakan ini tidak dapat dibatalkan. Pastikan alasan penolakan sudah tepat.</p>
                            <button type="submit"
                                class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition"
                                onclick="return confirm('Yakin ingin menolak pengajuan ini?')">
                                Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            @endif
            @endcan

        {{-- ===== AGUNAN (COLLATERAL REGISTRY) ===== --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Agunan / Jaminan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Daftar agunan yang diserahkan untuk pengajuan ini</p>
                </div>
                @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN_DATA']))
                <a href="{{ route('admin.loans.applications.collaterals.index', $application) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                    Kelola Agunan
                </a>
                @endif
            </div>
            @php
                $collaterals = \Illuminate\Support\Facades\Schema::hasTable('loan_collaterals')
                    ? $application->collaterals()->with('creator')->get()
                    : collect();
            @endphp
            @if($collaterals->isEmpty())
                <p class="px-5 py-4 text-sm text-gray-400">Belum ada agunan terdaftar.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                                <th class="px-4 py-2 text-left font-semibold">Jenis</th>
                                <th class="px-4 py-2 text-left font-semibold">Deskripsi</th>
                                <th class="px-4 py-2 text-right font-semibold">Nilai Taksiran</th>
                                <th class="px-4 py-2 text-center font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($collaterals as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $c->type_label }}</td>
                                <td class="px-4 py-2 text-gray-600 text-xs max-w-xs truncate">{{ $c->description ?? '—' }}</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-800">Rp {{ number_format($c->estimated_value, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($c->status === 'HELD')
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Ditahan</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Dilepas</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-2 border-t border-gray-100 text-xs text-gray-500 flex justify-between">
                    <span>{{ $collaterals->count() }} agunan</span>
                    <span class="font-semibold text-gray-700">Total: Rp {{ number_format($collaterals->sum('estimated_value'), 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
