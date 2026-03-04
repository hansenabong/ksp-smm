<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">Pinjaman / Pengajuan</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pengajuan #{{ $application->id }}</h2>
            </div>
            <div class="flex items-center gap-2">
                @if($application->status === 'APPROVED')
                    <a href="{{ route('admin.loans.applications.agreement', $application) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Cetak Perjanjian
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
                    'PENDING'  => ['bg' => 'bg-yellow-50 text-yellow-700 ring-yellow-200', 'dot' => 'bg-yellow-400'],
                    'APPROVED' => ['bg' => 'bg-green-50 text-green-700 ring-green-200',  'dot' => 'bg-green-400'],
                    'REJECTED' => ['bg' => 'bg-red-50 text-red-700 ring-red-200',        'dot' => 'bg-red-400'],
                    default    => ['bg' => 'bg-gray-50 text-gray-600 ring-gray-200',     'dot' => 'bg-gray-400'],
                };
                $monthly_interest = $application->principal * ($application->interest_monthly_percent / 100);
                $monthly_pokok    = $application->tenor_months > 0 ? $application->principal / $application->tenor_months : 0;
                $est_angsuran     = $monthly_pokok + $monthly_interest + $application->admin_monthly + $application->insurance_monthly;
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
                        <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pemohon</div>
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
                            <div class="text-xs text-gray-400 mb-1">Bunga Flat</div>
                            <div class="font-bold text-gray-800 text-sm">{{ $application->interest_monthly_percent }}%<span class="text-xs font-normal text-gray-400">/bln</span></div>
                            @if($application->admin_monthly)
                                <div class="text-xs text-gray-400 mt-0.5">Admin: Rp {{ number_format($application->admin_monthly, 0, ',', '.') }}</div>
                            @endif
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

            {{-- ===== FORM DATA SECTIONS ===== --}}
            @php $fd = $application->form_data ?? []; @endphp
            @if(!empty(array_filter($fd)))

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

            {{-- Data Agunan --}}
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

            @endif {{-- end array_filter --}}

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

            {{-- ===== APPROVE / REJECT ===== --}}
            @if ($application->status === 'PENDING')
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

    </div>
</x-admin-layout>
