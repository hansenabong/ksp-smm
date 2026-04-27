<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <div class="text-xs text-gray-400 mb-0.5">
                    Pinjaman / Pengajuan /
                    <a href="{{ route('admin.loans.applications.show', $application) }}" class="hover:underline">
                        #{{ $application->id }}
                    </a>
                </div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @php
                        $docLabel = match($docType) {
                            'ol-sertipikat' => 'OL — Penawaran Pembiayaan (Sertipikat)',
                            'ol-kendaraan'  => 'OL — Penawaran Pembiayaan (Kendaraan)',
                            'pk-sertipikat' => 'PK A — Perjanjian Kredit (Sertipikat)',
                            'pk-kendaraan'  => 'PK B — Perjanjian Kredit (Kendaraan)',
                            default         => 'Edit Draft Dokumen',
                        };
                    @endphp
                    {{ $docLabel }}
                </h2>
            </div>
            {{-- Print buttons --}}
            <div class="flex items-center gap-2 flex-wrap justify-end">
                @if(in_array($docType, ['ol-sertipikat', 'ol-kendaraan']))
                    @php $olTypeParam = str_contains($docType, 'kendaraan') ? 'kendaraan' : 'sertipikat'; @endphp
                    <a href="{{ route('admin.loans.applications.penawaran', $application) }}?type={{ $olTypeParam }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak OL PDF
                    </a>
                @elseif($docType === 'pk-sertipikat')
                    <a href="{{ route('admin.loans.applications.agreement', $application) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak PK PDF
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement.word', $application) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Word
                    </a>
                @elseif($docType === 'pk-kendaraan')
                    <a href="{{ route('admin.loans.applications.agreement.vehicle', $application) }}"
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak PK PDF
                    </a>
                    <a href="{{ route('admin.loans.applications.agreement.vehicle.word', $application) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Word
                    </a>
                @endif
                <a href="{{ route('admin.loans.applications.show', $application) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.loans.applications.doc_review.save', $application) }}">
            @csrf

            {{-- ── NOMOR DOKUMEN ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">Nomor Dokumen</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if(in_array($docType, ['ol-sertipikat','ol-kendaraan']))
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Surat OL</label>
                        <input type="text" name="nomor_surat_ol"
                               value="{{ $fd['nomor_surat_ol'] ?? ('SPP-'.now()->format('Y').'/'.str_pad($application->id,4,'0',STR_PAD_LEFT)) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @else
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nomor PK</label>
                        <input type="text" name="nomor_pk"
                               value="{{ $fd['nomor_pk'] ?? ('PK-'.$application->trx_date->format('Y').'/'.str_pad($application->id,4,'0',STR_PAD_LEFT)) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tujuan Pembiayaan</label>
                        <input type="text" name="tujuan_pembiayaan"
                               value="{{ $fd['tujuan_pembiayaan'] ?? $application->notes ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- ── PASANGAN (PK only) ─────────────────────────────────────────── --}}
            @if(in_array($docType, ['pk-sertipikat','pk-kendaraan']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">Data Pasangan / Suami / Istri</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pasangan</label>
                        <input type="text" name="nama_pasangan"
                               value="{{ $fd['nama_pasangan'] ?? $fd['nama_suami'] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alamat Pasangan</label>
                        <input type="text" name="alamat_pasangan"
                               value="{{ $fd['alamat_pasangan'] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">RT Pasangan</label>
                        <input type="text" name="rt_pasangan"
                               value="{{ $fd['rt_pasangan'] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">RW Pasangan</label>
                        <input type="text" name="rw_pasangan"
                               value="{{ $fd['rw_pasangan'] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
            @endif

            {{-- ── AGUNAN / SERTIPIKAT ────────────────────────────────────────── --}}
            @if(in_array($docType, ['ol-sertipikat','pk-sertipikat']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">Data Agunan — Sertipikat</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $sFlds = [
                            'no_hak'              => 'No. Hak',
                            'luas_tanah'          => 'Luas Tanah (m²)',
                            'atas_nama_agunan'    => 'Atas Nama (Sertifikat)',
                            'komplek_agunan'      => 'Nama Komplek / Kampung',
                            'jalan_agunan'        => 'Jalan',
                            'no_agunan'           => 'Nomor',
                            'rt_agunan'           => 'RT',
                            'rw_agunan'           => 'RW',
                            'kelurahan_agunan'    => 'Kelurahan',
                            'kecamatan_agunan'    => 'Kecamatan',
                            'kota_agunan'         => 'Kota / Kabupaten',
                            'penerbit_sertifikat' => 'Diterbitkan Oleh (BPN)',
                            'nib_agunan'          => 'NIB (Nomor Identifikasi Bidang)',
                            'nop_agunan'          => 'NOP (SPPT PBB)',
                            'tgl_sertifikat'      => 'Tanggal Surat Ukur',
                            'njop_agunan'         => 'NJOP (Rp)',
                        ];
                    @endphp
                    @foreach($sFlds as $key => $label)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                        <input type="text" name="{{ $key }}"
                               value="{{ $fd[$key] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── KENDARAAN ──────────────────────────────────────────────────── --}}
            @if(in_array($docType, ['ol-kendaraan','pk-kendaraan']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-700 text-sm">Data Kendaraan</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $kFlds = [
                            'jenis_kendaraan'  => 'Jenis Kendaraan',
                            'merk_kendaraan'   => 'Merk',
                            'type_kendaraan'   => 'Type',
                            'tahun_kendaraan'  => 'Tahun',
                            'model_kendaraan'  => 'Model',
                            'warna_kendaraan'  => 'Warna',
                            'bahan_bakar'      => 'Bahan Bakar',
                            'no_rangka'        => 'No. Rangka',
                            'no_mesin'         => 'No. Mesin',
                            'no_faktur'        => 'No. Faktur',
                            'no_polisi'        => 'No. Polisi',
                            'no_bpkb'          => 'No. BPKB',
                            'atas_nama_bpkb'   => 'Atas Nama BPKB',
                        ];
                    @endphp
                    @foreach($kFlds as $key => $label)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                        <input type="text" name="{{ $key }}"
                               value="{{ $fd[$key] ?? '' }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── SAVE BUTTON ────────────────────────────────────────────────── --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.loans.applications.show', $application) }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
