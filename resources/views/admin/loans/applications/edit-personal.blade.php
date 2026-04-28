<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs text-amber-600 font-medium mb-0.5">SUPER ADMIN — Edit Data Pribadi</div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Data Pribadi Pengajuan #{{ $application->id }}
                    <span class="ml-2 text-xs font-normal px-2 py-0.5 rounded-full bg-green-100 text-green-700">DISETUJUI</span>
                </h2>
            </div>
            <a href="{{ route('admin.loans.applications.show', $application) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    {{-- Notice: financial terms are locked --}}
    <div class="mb-4 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <div class="font-semibold">Hanya data pribadi yang dapat diubah</div>
            <div class="mt-0.5 text-amber-700">
                Syarat keuangan (pokok, tenor, bunga, produk) telah terkunci setelah persetujuan dan
                <strong>tidak dapat diubah</strong>. Perubahan di halaman ini hanya mempengaruhi
                data identitas, pekerjaan, agunan, dan dokumen pendukung.
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            <div class="font-semibold mb-1">Ada error:</div>
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $fd = $application->form_data ?? []; @endphp

    <form method="POST" action="{{ route('admin.loans.applications.update_personal', $application) }}"
          class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ===== TUJUAN PEMBIAYAAN ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Tujuan Pembiayaan</h3>
            <div>
                <label class="text-sm">Tujuan Penggunaan Dana <span class="text-xs text-gray-400">(untuk Pasal 2 Perjanjian Pembiayaan)</span></label>
                <input type="text" name="form_data[tujuan_pembiayaan]"
                    value="{{ old('form_data.tujuan_pembiayaan', $fd['tujuan_pembiayaan'] ?? '') }}"
                    class="w-full border rounded px-3 py-2"
                    placeholder="cth: pembelian rumah tinggal / renovasi rumah / modal usaha">
            </div>
        </div>

        {{-- ===== DATA PRIBADI PEMOHON ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Pribadi Pemohon</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="text-sm">Nama Lengkap (sesuai KTP)</label>
                    <input type="text" name="form_data[nama_lengkap]"
                        value="{{ old('form_data.nama_lengkap', $fd['nama_lengkap'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Jenis Kelamin</label>
                    <select name="form_data[kelamin]" class="w-full border rounded px-3 py-2">
                        <option value="">Pilih</option>
                        <option value="L" @selected(old('form_data.kelamin', $fd['kelamin'] ?? '') == 'L')>Laki-laki</option>
                        <option value="P" @selected(old('form_data.kelamin', $fd['kelamin'] ?? '') == 'P')>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">No. KTP</label>
                    <input type="text" name="form_data[no_ktp]"
                        value="{{ old('form_data.no_ktp', $fd['no_ktp'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">Tgl. Lahir</label>
                    <input type="date" name="form_data[tgl_lahir]"
                        value="{{ old('form_data.tgl_lahir', $fd['tgl_lahir'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div x-data="{ seumurHidup: {{ old('form_data.ktp_seumur_hidup', $fd['ktp_seumur_hidup'] ?? false) ? 'true' : 'false' }} }">
                    <label class="text-sm">KTP Berlaku s/d</label>
                    <input type="date" name="form_data[ktp_berlaku]"
                        :value="seumurHidup ? '' : '{{ old('form_data.ktp_berlaku', $fd['ktp_berlaku'] ?? '') }}'"
                        :disabled="seumurHidup"
                        :class="seumurHidup ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                        class="w-full border rounded px-3 py-2">
                    <label class="flex items-center gap-1.5 mt-1 text-xs text-gray-600 cursor-pointer">
                        <input type="checkbox" name="form_data[ktp_seumur_hidup]" value="1"
                            x-model="seumurHidup" class="rounded border-gray-300">
                        <span>e-KTP / Seumur Hidup</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Status Perkawinan</label>
                    <select name="form_data[status_perkawinan]" class="w-full border rounded px-3 py-2">
                        <option value="">Pilih</option>
                        @foreach(['Belum Kawin','Kawin','Cerai Hidup','Cerai Mati'] as $opt)
                            <option value="{{ $opt }}"
                                @selected(old('form_data.status_perkawinan', $fd['status_perkawinan'] ?? '') == $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm">Pendidikan</label>
                    <select name="form_data[pendidikan]" class="w-full border rounded px-3 py-2">
                        <option value="">Pilih</option>
                        @foreach(['SD','SMP','SMA/SMK','D1/D2/D3','S1','S2','S3'] as $pend)
                            <option value="{{ $pend }}"
                                @selected(old('form_data.pendidikan', $fd['pendidikan'] ?? '') == $pend)>{{ $pend }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm">Nama Gadis Ibu Kandung</label>
                    <input type="text" name="form_data[nama_ibu_kandung]"
                        value="{{ old('form_data.nama_ibu_kandung', $fd['nama_ibu_kandung'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div>
                <label class="text-sm">Alamat Rumah Sekarang</label>
                <input type="text" name="form_data[alamat]"
                    value="{{ old('form_data.alamat', $fd['alamat'] ?? '') }}"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm">RT</label>
                    <input type="text" name="form_data[rt]"
                        value="{{ old('form_data.rt', $fd['rt'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="5">
                </div>
                <div>
                    <label class="text-sm">RW</label>
                    <input type="text" name="form_data[rw]"
                        value="{{ old('form_data.rw', $fd['rw'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="5">
                </div>
                <div>
                    <label class="text-sm">Kode Pos</label>
                    <input type="text" name="form_data[kode_pos]"
                        value="{{ old('form_data.kode_pos', $fd['kode_pos'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="10">
                </div>
                <div>
                    <label class="text-sm">Kota</label>
                    <input type="text" name="form_data[kota]"
                        value="{{ old('form_data.kota', $fd['kota'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Telp. Rumah</label>
                    <input type="text" name="form_data[telp_rumah]"
                        value="{{ old('form_data.telp_rumah', $fd['telp_rumah'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">No. HP</label>
                    <input type="text" name="form_data[hp]"
                        value="{{ old('form_data.hp', $fd['hp'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">Kepemilikan Rumah</label>
                    <select name="form_data[kepemilikan_rumah]" class="w-full border rounded px-3 py-2">
                        <option value="">Pilih</option>
                        @foreach(['Milik Sendiri','Kontrak/Sewa','Orang Tua','Dinas','Lainnya'] as $k)
                            <option value="{{ $k }}"
                                @selected(old('form_data.kepemilikan_rumah', $fd['kepemilikan_rumah'] ?? '') == $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 md:w-1/2">
                <div>
                    <label class="text-sm">Lama Menempati (Tahun)</label>
                    <input type="number" name="form_data[lama_menempati_thn]"
                        value="{{ old('form_data.lama_menempati_thn', $fd['lama_menempati_thn'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
                <div>
                    <label class="text-sm">Bulan</label>
                    <input type="number" name="form_data[lama_menempati_bln]"
                        value="{{ old('form_data.lama_menempati_bln', $fd['lama_menempati_bln'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" min="0" max="11">
                </div>
            </div>
        </div>

        {{-- ===== DATA ISTRI / SUAMI ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Istri / Suami Pemohon</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="text-sm">Nama Istri / Suami</label>
                    <input type="text" name="form_data[nama_pasangan]"
                        value="{{ old('form_data.nama_pasangan', $fd['nama_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Jumlah Tanggungan</label>
                    <input type="number" name="form_data[jumlah_tanggungan]"
                        value="{{ old('form_data.jumlah_tanggungan', $fd['jumlah_tanggungan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" min="0">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">No. KTP Istri / Suami</label>
                    <input type="text" name="form_data[no_ktp_pasangan]"
                        value="{{ old('form_data.no_ktp_pasangan', $fd['no_ktp_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">Tgl. Lahir</label>
                    <input type="date" name="form_data[tgl_lahir_pasangan]"
                        value="{{ old('form_data.tgl_lahir_pasangan', $fd['tgl_lahir_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div x-data="{ seumurHidup: {{ old('form_data.ktp_seumur_hidup_pasangan', $fd['ktp_seumur_hidup_pasangan'] ?? false) ? 'true' : 'false' }} }">
                    <label class="text-sm">KTP Berlaku s/d</label>
                    <input type="date" name="form_data[ktp_berlaku_pasangan]"
                        :value="seumurHidup ? '' : '{{ old('form_data.ktp_berlaku_pasangan', $fd['ktp_berlaku_pasangan'] ?? '') }}'"
                        :disabled="seumurHidup"
                        :class="seumurHidup ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : ''"
                        class="w-full border rounded px-3 py-2">
                    <label class="flex items-center gap-1.5 mt-1 text-xs text-gray-600 cursor-pointer">
                        <input type="checkbox" name="form_data[ktp_seumur_hidup_pasangan]" value="1"
                            x-model="seumurHidup" class="rounded border-gray-300">
                        <span>e-KTP / Seumur Hidup</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- ===== KONTAK DARURAT ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Kontak Darurat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="text-sm">Nama</label>
                    <input type="text" name="form_data[nama_darurat]"
                        value="{{ old('form_data.nama_darurat', $fd['nama_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Telp / HP</label>
                    <input type="text" name="form_data[telp_darurat]"
                        value="{{ old('form_data.telp_darurat', $fd['telp_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
            </div>
            <div>
                <label class="text-sm">Alamat</label>
                <input type="text" name="form_data[alamat_darurat]"
                    value="{{ old('form_data.alamat_darurat', $fd['alamat_darurat'] ?? '') }}"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm">RT</label>
                    <input type="text" name="form_data[rt_darurat]"
                        value="{{ old('form_data.rt_darurat', $fd['rt_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="5">
                </div>
                <div>
                    <label class="text-sm">RW</label>
                    <input type="text" name="form_data[rw_darurat]"
                        value="{{ old('form_data.rw_darurat', $fd['rw_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="5">
                </div>
                <div>
                    <label class="text-sm">Kode Pos</label>
                    <input type="text" name="form_data[kode_pos_darurat]"
                        value="{{ old('form_data.kode_pos_darurat', $fd['kode_pos_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="10">
                </div>
                <div>
                    <label class="text-sm">Hubungan</label>
                    <input type="text" name="form_data[hubungan_darurat]"
                        value="{{ old('form_data.hubungan_darurat', $fd['hubungan_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Desa / Kelurahan</label>
                    <input type="text" name="form_data[kelurahan_darurat]"
                        value="{{ old('form_data.kelurahan_darurat', $fd['kelurahan_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Kecamatan</label>
                    <input type="text" name="form_data[kecamatan_darurat]"
                        value="{{ old('form_data.kecamatan_darurat', $fd['kecamatan_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Kota</label>
                    <input type="text" name="form_data[kota_darurat]"
                        value="{{ old('form_data.kota_darurat', $fd['kota_darurat'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>
        </div>

        {{-- ===== DATA PEKERJAAN PEMOHON ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Pekerjaan Pemohon</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm">Pekerjaan / Profesi</label>
                    <input type="text" name="form_data[pekerjaan]"
                        value="{{ old('form_data.pekerjaan', $fd['pekerjaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Perusahaan / Instansi</label>
                    <input type="text" name="form_data[perusahaan]"
                        value="{{ old('form_data.perusahaan', $fd['perusahaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div>
                <label class="text-sm">Alamat Perusahaan</label>
                <input type="text" name="form_data[alamat_perusahaan]"
                    value="{{ old('form_data.alamat_perusahaan', $fd['alamat_perusahaan'] ?? '') }}"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm">Kota</label>
                    <input type="text" name="form_data[kota_perusahaan]"
                        value="{{ old('form_data.kota_perusahaan', $fd['kota_perusahaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Kode Pos</label>
                    <input type="text" name="form_data[kode_pos_perusahaan]"
                        value="{{ old('form_data.kode_pos_perusahaan', $fd['kode_pos_perusahaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="10">
                </div>
                <div>
                    <label class="text-sm">Telp.</label>
                    <input type="text" name="form_data[telp_perusahaan]"
                        value="{{ old('form_data.telp_perusahaan', $fd['telp_perusahaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">Fax</label>
                    <input type="text" name="form_data[fax_perusahaan]"
                        value="{{ old('form_data.fax_perusahaan', $fd['fax_perusahaan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Jenis Usaha</label>
                    <input type="text" name="form_data[jenis_usaha]"
                        value="{{ old('form_data.jenis_usaha', $fd['jenis_usaha'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Jabatan</label>
                    <input type="text" name="form_data[jabatan]"
                        value="{{ old('form_data.jabatan', $fd['jabatan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Total Masa Kerja</label>
                    <input type="text" name="form_data[masa_kerja]"
                        value="{{ old('form_data.masa_kerja', $fd['masa_kerja'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" placeholder="cth: 5 Tahun 3 Bulan">
                </div>
            </div>
        </div>

        {{-- ===== DATA PEKERJAAN ISTRI / SUAMI ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Pekerjaan Istri / Suami</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm">Pekerjaan / Profesi</label>
                    <input type="text" name="form_data[pekerjaan_pasangan]"
                        value="{{ old('form_data.pekerjaan_pasangan', $fd['pekerjaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Perusahaan / Instansi</label>
                    <input type="text" name="form_data[perusahaan_pasangan]"
                        value="{{ old('form_data.perusahaan_pasangan', $fd['perusahaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div>
                <label class="text-sm">Alamat Perusahaan</label>
                <input type="text" name="form_data[alamat_perusahaan_pasangan]"
                    value="{{ old('form_data.alamat_perusahaan_pasangan', $fd['alamat_perusahaan_pasangan'] ?? '') }}"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-sm">Kota</label>
                    <input type="text" name="form_data[kota_perusahaan_pasangan]"
                        value="{{ old('form_data.kota_perusahaan_pasangan', $fd['kota_perusahaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Kode Pos</label>
                    <input type="text" name="form_data[kode_pos_perusahaan_pasangan]"
                        value="{{ old('form_data.kode_pos_perusahaan_pasangan', $fd['kode_pos_perusahaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="10">
                </div>
                <div>
                    <label class="text-sm">Telp.</label>
                    <input type="text" name="form_data[telp_perusahaan_pasangan]"
                        value="{{ old('form_data.telp_perusahaan_pasangan', $fd['telp_perusahaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
                <div>
                    <label class="text-sm">Fax</label>
                    <input type="text" name="form_data[fax_perusahaan_pasangan]"
                        value="{{ old('form_data.fax_perusahaan_pasangan', $fd['fax_perusahaan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" maxlength="20">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm">Jenis Usaha</label>
                    <input type="text" name="form_data[jenis_usaha_pasangan]"
                        value="{{ old('form_data.jenis_usaha_pasangan', $fd['jenis_usaha_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Jabatan</label>
                    <input type="text" name="form_data[jabatan_pasangan]"
                        value="{{ old('form_data.jabatan_pasangan', $fd['jabatan_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-sm">Total Masa Kerja</label>
                    <input type="text" name="form_data[masa_kerja_pasangan]"
                        value="{{ old('form_data.masa_kerja_pasangan', $fd['masa_kerja_pasangan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2" placeholder="cth: 5 Tahun 3 Bulan">
                </div>
            </div>
        </div>

        {{-- ===== DATA AGUNAN ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Agunan</h3>

            <div class="space-y-3">
                <div class="text-sm font-medium text-gray-600">1. Tanah dan Bangunan</div>
                <div>
                    <label class="text-sm">Lokasi Agunan</label>
                    <input type="text" name="form_data[lokasi_agunan]"
                        value="{{ old('form_data.lokasi_agunan', $fd['lokasi_agunan'] ?? '') }}"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm">Status</label>
                        <select name="form_data[status_agunan]" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih</option>
                            @foreach(['SHM','SHGB','SHP','HGB','Girik/Letter C','Lainnya'] as $s)
                                <option value="{{ $s }}"
                                    @selected(old('form_data.status_agunan', $fd['status_agunan'] ?? '') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm">Atas Nama</label>
                        <input type="text" name="form_data[atas_nama_agunan]"
                            value="{{ old('form_data.atas_nama_agunan', $fd['atas_nama_agunan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Nilai / Harga (NJOP) Rp</label>
                        <input type="text" data-money name="form_data[nilai_agunan]"
                            value="{{ old('form_data.nilai_agunan', $fd['nilai_agunan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-sm">No. Hak (SHM/SHGB)</label>
                        <input type="text" name="form_data[no_hak]"
                            value="{{ old('form_data.no_hak', $fd['no_hak'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="cth: 01234">
                    </div>
                    <div>
                        <label class="text-sm">Luas Tanah (m²)</label>
                        <input type="text" name="form_data[luas_tanah]"
                            value="{{ old('form_data.luas_tanah', $fd['luas_tanah'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Luas Bangunan (m²)</label>
                        <input type="text" name="form_data[luas_bangunan]"
                            value="{{ old('form_data.luas_bangunan', $fd['luas_bangunan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">No. IMB</label>
                        <input type="text" name="form_data[no_imb]"
                            value="{{ old('form_data.no_imb', $fd['no_imb'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                {{-- Alamat objek pembiayaan (Pasal 3 PK) --}}
                <div class="pt-3 border-t">
                    <div class="text-xs font-medium text-gray-500 mb-2">Alamat Lokasi Objek Pembiayaan (Pasal 3)</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Komplek / Perumahan</label>
                            <input type="text" name="form_data[komplek_agunan]"
                                value="{{ old('form_data.komplek_agunan', $fd['komplek_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Jalan</label>
                            <input type="text" name="form_data[jalan_agunan]"
                                value="{{ old('form_data.jalan_agunan', $fd['jalan_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Blok / No.</label>
                            <div class="flex gap-2">
                                <input type="text" name="form_data[blok_agunan]"
                                    value="{{ old('form_data.blok_agunan', $fd['blok_agunan'] ?? '') }}"
                                    class="w-1/2 border rounded px-3 py-2" placeholder="Blok">
                                <input type="text" name="form_data[no_agunan]"
                                    value="{{ old('form_data.no_agunan', $fd['no_agunan'] ?? '') }}"
                                    class="w-1/2 border rounded px-3 py-2" placeholder="No.">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-3">
                        <div>
                            <label class="text-sm">RT</label>
                            <input type="text" name="form_data[rt_agunan]"
                                value="{{ old('form_data.rt_agunan', $fd['rt_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">RW</label>
                            <input type="text" name="form_data[rw_agunan]"
                                value="{{ old('form_data.rw_agunan', $fd['rw_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">Desa / Kelurahan</label>
                            <input type="text" name="form_data[kelurahan_agunan]"
                                value="{{ old('form_data.kelurahan_agunan', $fd['kelurahan_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kecamatan</label>
                            <input type="text" name="form_data[kecamatan_agunan]"
                                value="{{ old('form_data.kecamatan_agunan', $fd['kecamatan_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kab. / Kota</label>
                            <input type="text" name="form_data[kota_agunan]"
                                value="{{ old('form_data.kota_agunan', $fd['kota_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Provinsi</label>
                            <input type="text" name="form_data[provinsi_agunan]"
                                value="{{ old('form_data.provinsi_agunan', $fd['provinsi_agunan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2"
                                placeholder="cth: DKI Jakarta">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="text-sm">Diterbitkan Oleh (BPN / Kantor Pertanahan)</label>
                            <input type="text" name="form_data[penerbit_sertifikat]"
                                value="{{ old('form_data.penerbit_sertifikat', $fd['penerbit_sertifikat'] ?? '') }}"
                                class="w-full border rounded px-3 py-2"
                                placeholder="cth: BPN Kota Jakarta Selatan">
                        </div>
                        <div>
                            <label class="text-sm">Tanggal Penerbitan Sertifikat</label>
                            <input type="date" name="form_data[tgl_sertifikat]"
                                value="{{ old('form_data.tgl_sertifikat', $fd['tgl_sertifikat'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3 border-t pt-3">
                <div class="text-sm font-medium text-gray-600">2. Kendaraan dan BPKB</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm">Jenis Kendaraan</label>
                        <input type="text" name="form_data[jenis_kendaraan]"
                            value="{{ old('form_data.jenis_kendaraan', $fd['jenis_kendaraan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Tahun</label>
                        <input type="number" name="form_data[tahun_kendaraan]"
                            value="{{ old('form_data.tahun_kendaraan', $fd['tahun_kendaraan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" min="1970" max="{{ date('Y') + 1 }}">
                    </div>
                    <div>
                        <label class="text-sm">Warna</label>
                        <input type="text" name="form_data[warna_kendaraan]"
                            value="{{ old('form_data.warna_kendaraan', $fd['warna_kendaraan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-sm">No. BPKB</label>
                        <input type="text" name="form_data[no_bpkb]"
                            value="{{ old('form_data.no_bpkb', $fd['no_bpkb'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">Atas Nama</label>
                        <input type="text" name="form_data[atas_nama_bpkb]"
                            value="{{ old('form_data.atas_nama_bpkb', $fd['atas_nama_bpkb'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">No. Mesin</label>
                        <input type="text" name="form_data[no_mesin]"
                            value="{{ old('form_data.no_mesin', $fd['no_mesin'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm">No. Rangka</label>
                        <input type="text" name="form_data[no_rangka]"
                            value="{{ old('form_data.no_rangka', $fd['no_rangka'] ?? '') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                {{-- Spesifikasi Detail Kendaraan (Pasal 3 PK B) --}}
                <div class="border-t pt-3 space-y-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Spesifikasi Kendaraan (Pasal 3 PK B)</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Merk / Pabrikan</label>
                            <input type="text" name="form_data[merk_kendaraan]"
                                value="{{ old('form_data.merk_kendaraan', $fd['merk_kendaraan'] ?? '') }}"
                                placeholder="cth: Toyota / Honda"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Type / Varian</label>
                            <input type="text" name="form_data[type_kendaraan]"
                                value="{{ old('form_data.type_kendaraan', $fd['type_kendaraan'] ?? '') }}"
                                placeholder="cth: Avanza 1.3 G MT"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Model</label>
                            <input type="text" name="form_data[model_kendaraan]"
                                value="{{ old('form_data.model_kendaraan', $fd['model_kendaraan'] ?? '') }}"
                                placeholder="cth: MPV / Sedan / SUV"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm">Bahan Bakar</label>
                            <input type="text" name="form_data[bahan_bakar_kendaraan]"
                                value="{{ old('form_data.bahan_bakar_kendaraan', $fd['bahan_bakar_kendaraan'] ?? '') }}"
                                placeholder="cth: BBM / Solar / Listrik"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">No. Polisi</label>
                            <input type="text" name="form_data[no_polisi]"
                                value="{{ old('form_data.no_polisi', $fd['no_polisi'] ?? '') }}"
                                placeholder="cth: B 1234 XYZ"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">No. Faktur</label>
                            <input type="text" name="form_data[no_faktur_kendaraan]"
                                value="{{ old('form_data.no_faktur_kendaraan', $fd['no_faktur_kendaraan'] ?? '') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== DATA PENDAPATAN & PENGELUARAN ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Data Pendapatan dan Pengeluaran per Bulan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="text-sm font-medium text-gray-600">Sumber Pendapatan / Bulan</div>
                    <div>
                        <label class="text-sm">1. Penghasilan Pemohon (Rp)</label>
                        <input type="text" data-money name="form_data[penghasilan_pemohon]"
                            value="{{ old('form_data.penghasilan_pemohon', $fd['penghasilan_pemohon'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm">2. Penghasilan Istri / Suami (Rp)</label>
                        <input type="text" data-money name="form_data[penghasilan_pasangan]"
                            value="{{ old('form_data.penghasilan_pasangan', $fd['penghasilan_pasangan'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm">3. Penghasilan Lain-lain (Rp)</label>
                        <input type="text" data-money name="form_data[penghasilan_lain]"
                            value="{{ old('form_data.penghasilan_lain', $fd['penghasilan_lain'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="text-sm font-medium text-gray-600">Sumber Pengeluaran / Bulan</div>
                    <div>
                        <label class="text-sm">1. Biaya Sewa (Rp)</label>
                        <input type="text" data-money name="form_data[biaya_sewa]"
                            value="{{ old('form_data.biaya_sewa', $fd['biaya_sewa'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm">2. Biaya Hidup (Rp)</label>
                        <input type="text" data-money name="form_data[biaya_hidup]"
                            value="{{ old('form_data.biaya_hidup', $fd['biaya_hidup'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm">3. Angsuran Pinjaman Lain (Rp)</label>
                        <input type="text" data-money name="form_data[angsuran_lain]"
                            value="{{ old('form_data.angsuran_lain', $fd['angsuran_lain'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm">4. Biaya Lain-lain (Rp)</label>
                        <input type="text" data-money name="form_data[biaya_lain]"
                            value="{{ old('form_data.biaya_lain', $fd['biaya_lain'] ?? '') }}"
                            class="w-full border rounded px-3 py-2" placeholder="0">
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== DOKUMEN ===== --}}
        <div class="bg-white p-6 rounded shadow space-y-4">
            <h3 class="font-semibold text-gray-800 border-b pb-2">Dokumen</h3>

            @if ($application->files->isNotEmpty())
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-2">Dokumen Tersimpan</div>
                    <div class="divide-y divide-gray-100 border rounded-lg overflow-hidden">
                        @foreach ($application->files as $file)
                            <div class="flex items-center justify-between px-4 py-2 bg-gray-50 hover:bg-gray-100">
                                <div class="flex items-center gap-3 min-w-0">
                                    @php
                                        $badgeColor = match($file->category) {
                                            'KTP'    => 'bg-blue-100 text-blue-700',
                                            'KK'     => 'bg-purple-100 text-purple-700',
                                            'AGUNAN' => 'bg-amber-100 text-amber-700',
                                            default  => 'bg-gray-200 text-gray-600',
                                        };
                                    @endphp
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $badgeColor }}">{{ $file->category }}</span>
                                    <span class="text-sm text-gray-700 truncate">{{ $file->original_name }}</span>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ number_format($file->size / 1024, 0) }} KB</span>
                                </div>
                                <div class="flex items-center gap-2 ml-4 shrink-0">
                                    <a href="{{ route('admin.loans.applications.files.download', [$application, $file]) }}"
                                       class="text-xs text-indigo-600 hover:underline">Unduh</a>
                                    <button type="button"
                                            onclick="if(confirm('Hapus file \'{{ addslashes($file->original_name) }}\'?')) document.getElementById('del-file-{{ $file->id }}').submit();"
                                            class="text-xs text-red-500 hover:text-red-700 hover:underline">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-2">
                <div class="text-sm font-medium text-gray-700 mb-3">Tambah Dokumen</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm">KTP</label>
                        <input type="file" name="doc_ktp" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full border rounded px-3 py-2 text-sm">
                        @error('doc_ktp') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Kartu Keluarga</label>
                        <input type="file" name="doc_kk" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full border rounded px-3 py-2 text-sm">
                        @error('doc_kk') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="text-sm">Agunan (bisa lebih dari satu)</label>
                        <input type="file" name="doc_agunan[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                            class="w-full border rounded px-3 py-2 text-sm">
                        @error('doc_agunan.*') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-sm">Dokumen Lainnya (bisa lebih dari satu)</label>
                    <input type="file" name="doc_others[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                        class="w-full border rounded px-3 py-2 text-sm">
                    @error('doc_others.*') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="text-xs text-gray-400 mt-1">Format: JPG, PNG, PDF. Maks 5 MB per file.</div>
            </div>
        </div>

        {{-- ===== NOTARIS ===== --}}
        <details class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                 {{ $application->notaris_name || $application->notaris_deed_number ? 'open' : '' }}>
            <summary class="px-5 py-4 cursor-pointer flex items-center justify-between text-sm font-semibold text-gray-700 hover:bg-gray-50">
                <span>Notaris <span class="text-xs text-gray-400 font-normal">(opsional)</span></span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>
            <div class="px-5 pb-5 pt-2 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Nama Notaris</label>
                    <input type="text" name="notaris_name"
                           value="{{ old('notaris_name', $application->notaris_name) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: H. Budi Santoso, SH">
                    @error('notaris_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">No. Akta</label>
                    <input type="text" name="notaris_deed_number"
                           value="{{ old('notaris_deed_number', $application->notaris_deed_number) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: 001/2026">
                    @error('notaris_deed_number') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Tanggal Akta</label>
                    <input type="date" name="notaris_deed_date"
                           value="{{ old('notaris_deed_date', $application->notaris_deed_date?->toDateString()) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                    @error('notaris_deed_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Kota / Kabupaten</label>
                    <input type="text" name="notaris_city"
                           value="{{ old('notaris_city', $application->notaris_city) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                           placeholder="cth: Surabaya">
                    @error('notaris_city') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </details>

        {{-- ===== ACTIONS ===== --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="px-5 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                Simpan Data Pribadi
            </button>
            <a href="{{ route('admin.loans.applications.show', $application) }}"
               class="px-5 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</a>
        </div>

    </form>

    {{-- Standalone delete forms (outside main form to avoid nesting) --}}
    @foreach($application->files as $file)
    <form id="del-file-{{ $file->id }}" method="POST"
          action="{{ route('admin.loans.applications.files.delete', [$application, $file]) }}"
          style="display:none">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

</x-admin-layout>
