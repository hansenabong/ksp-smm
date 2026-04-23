<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengajuan Pinjaman</h2>
    </x-slot>

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

            <form method="POST" action="{{ route('admin.loans.applications.store') }}" class="space-y-6"
                  enctype="multipart/form-data">
                @csrf

                {{-- ===== PERMOHONAN PEMBIAYAAN ===== --}}
                <div class="bg-white p-6 rounded shadow space-y-4">
                    <h3 class="font-semibold text-gray-800 border-b pb-2">Permohonan Pembiayaan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm">Tanggal</label>
                            <input type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Anggota</label>
                            <select name="member_id" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih Anggota</option>
                                @foreach ($members as $m)
                                    <option value="{{ $m->id }}" @selected(old('member_id') == $m->id)>
                                        {{ $m->member_no }} - {{ $m->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Produk Pinjaman</label>
                        <select id="loan_product_id" name="loan_product_id" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih Produk</option>
                            @foreach ($loanProducts as $lp)
                                <option value="{{ $lp->id }}" data-max-tenor="{{ $lp->max_tenor_months }}"
                                    data-rate-bp="{{ $lp->interest_rate_monthly_bp }}"
                                    data-method="{{ $lp->method }}"
                                    @selected(old('loan_product_id') == $lp->id)>
                                    {{ $lp->name }} ({{ $lp->method }})
                                </option>
                            @endforeach
                        </select>
                        <div class="text-xs text-gray-500 mt-1" id="loan_product_hint"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm">Jumlah yang Dimohon (Rp)</label>
                            <input type="text" data-money name="principal" value="{{ old('principal') }}"
                                class="w-full border rounded px-3 py-2" placeholder="0">
                        </div>
                        <div>
                            <label class="text-sm">Jangka Waktu (bulan)</label>
                            <input id="tenor_months" type="number" name="tenor_months"
                                value="{{ old('tenor_months', 12) }}" class="w-full border rounded px-3 py-2"
                                min="1" max="120">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm">Bunga / bulan (%)</label>
                            <input id="interest_monthly_percent" type="number" step="0.01"
                                name="interest_monthly_percent" value="{{ old('interest_monthly_percent') }}"
                                class="w-full border rounded px-3 py-2" min="0" onclick="this.select()">
                            <div class="text-xs text-gray-500 mt-1" id="interest_method_hint">Auto dari produk, masih bisa diubah</div>
                        </div>
                        <div>
                            <label class="text-sm">Asuransi / bulan (Rp)</label>
                            <input type="text" data-money name="insurance_monthly" value="{{ old('insurance_monthly', 0) }}"
                                class="w-full border rounded px-3 py-2" placeholder="0" onclick="this.select()">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Catatan (optional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            class="w-full border rounded px-3 py-2" maxlength="255">
                    </div>
                </div>

                {{-- ===== DATA PRIBADI PEMOHON ===== --}}
                <div class="bg-white p-6 rounded shadow space-y-4">
                    <h3 class="font-semibold text-gray-800 border-b pb-2">Data Pribadi Pemohon</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-sm">Nama Lengkap (sesuai KTP)</label>
                            <input type="text" name="form_data[nama_lengkap]"
                                value="{{ old('form_data.nama_lengkap') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Jenis Kelamin</label>
                            <select name="form_data[kelamin]" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih</option>
                                <option value="L" @selected(old('form_data.kelamin') == 'L')>Laki-laki</option>
                                <option value="P" @selected(old('form_data.kelamin') == 'P')>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">No. KTP</label>
                            <input type="text" name="form_data[no_ktp]"
                                value="{{ old('form_data.no_ktp') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">Tgl. Lahir</label>
                            <input type="date" name="form_data[tgl_lahir]"
                                value="{{ old('form_data.tgl_lahir') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div x-data="{ seumurHidup: {{ old('form_data.ktp_seumur_hidup') ? 'true' : 'false' }} }">
                            <label class="text-sm">KTP Berlaku s/d</label>
                            <input type="date" name="form_data[ktp_berlaku]"
                                :value="seumurHidup ? '' : '{{ old('form_data.ktp_berlaku') }}'"
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
                                <option value="Belum Kawin" @selected(old('form_data.status_perkawinan') == 'Belum Kawin')>Belum Kawin</option>
                                <option value="Kawin" @selected(old('form_data.status_perkawinan') == 'Kawin')>Kawin</option>
                                <option value="Cerai Hidup" @selected(old('form_data.status_perkawinan') == 'Cerai Hidup')>Cerai Hidup</option>
                                <option value="Cerai Mati" @selected(old('form_data.status_perkawinan') == 'Cerai Mati')>Cerai Mati</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm">Pendidikan</label>
                            <select name="form_data[pendidikan]" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih</option>
                                @foreach(['SD','SMP','SMA/SMK','D1/D2/D3','S1','S2','S3'] as $pend)
                                    <option value="{{ $pend }}" @selected(old('form_data.pendidikan') == $pend)>{{ $pend }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm">Nama Gadis Ibu Kandung</label>
                            <input type="text" name="form_data[nama_ibu_kandung]"
                                value="{{ old('form_data.nama_ibu_kandung') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Alamat Rumah Sekarang</label>
                        <input type="text" name="form_data[alamat]"
                            value="{{ old('form_data.alamat') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                        <div>
                            <label class="text-sm">RT</label>
                            <input type="text" name="form_data[rt]" value="{{ old('form_data.rt') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">RW</label>
                            <input type="text" name="form_data[rw]" value="{{ old('form_data.rw') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">Kelurahan</label>
                            <input type="text" name="form_data[kelurahan]" value="{{ old('form_data.kelurahan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kecamatan</label>
                            <input type="text" name="form_data[kecamatan]" value="{{ old('form_data.kecamatan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kota</label>
                            <input type="text" name="form_data[kota]" value="{{ old('form_data.kota') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kode Pos</label>
                            <input type="text" name="form_data[kode_pos]" value="{{ old('form_data.kode_pos') }}"
                                class="w-full border rounded px-3 py-2" maxlength="10">
                        </div>
                    </div>

                    {{-- Alamat KTP --}}
                    <div class="border-t pt-3 mt-2">
                        <label class="text-sm font-medium text-gray-600">Alamat Sesuai KTP</label>
                    </div>
                    <div>
                        <input type="text" name="form_data[alamat_ktp]"
                            value="{{ old('form_data.alamat_ktp') }}"
                            class="w-full border rounded px-3 py-2" placeholder="Alamat lengkap sesuai KTP">
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                        <div>
                            <label class="text-sm">RT</label>
                            <input type="text" name="form_data[rt_ktp]" value="{{ old('form_data.rt_ktp') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">RW</label>
                            <input type="text" name="form_data[rw_ktp]" value="{{ old('form_data.rw_ktp') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">Kelurahan</label>
                            <input type="text" name="form_data[kelurahan_ktp]" value="{{ old('form_data.kelurahan_ktp') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kecamatan</label>
                            <input type="text" name="form_data[kecamatan_ktp]" value="{{ old('form_data.kecamatan_ktp') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kota</label>
                            <input type="text" name="form_data[kota_ktp]" value="{{ old('form_data.kota_ktp') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kode Pos</label>
                            <input type="text" name="form_data[kode_pos_ktp]" value="{{ old('form_data.kode_pos_ktp') }}"
                                class="w-full border rounded px-3 py-2" maxlength="10">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Telp. Rumah</label>
                            <input type="text" name="form_data[telp_rumah]" value="{{ old('form_data.telp_rumah') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">No. HP</label>
                            <input type="text" name="form_data[hp]" value="{{ old('form_data.hp') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">Kepemilikan Rumah</label>
                            <select name="form_data[kepemilikan_rumah]" class="w-full border rounded px-3 py-2">
                                <option value="">Pilih</option>
                                @foreach(['Milik Sendiri','Kontrak/Sewa','Orang Tua','Dinas','Lainnya'] as $k)
                                    <option value="{{ $k }}" @selected(old('form_data.kepemilikan_rumah') == $k)>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:w-1/2">
                        <div>
                            <label class="text-sm">Lama Menempati (Tahun)</label>
                            <input type="number" name="form_data[lama_menempati_thn]"
                                value="{{ old('form_data.lama_menempati_thn') }}"
                                class="w-full border rounded px-3 py-2" min="0">
                        </div>
                        <div>
                            <label class="text-sm">Bulan</label>
                            <input type="number" name="form_data[lama_menempati_bln]"
                                value="{{ old('form_data.lama_menempati_bln') }}"
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
                                value="{{ old('form_data.nama_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Jumlah Tanggungan</label>
                            <input type="number" name="form_data[jumlah_tanggungan]"
                                value="{{ old('form_data.jumlah_tanggungan') }}"
                                class="w-full border rounded px-3 py-2" min="0">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">No. KTP Istri / Suami</label>
                            <input type="text" name="form_data[no_ktp_pasangan]"
                                value="{{ old('form_data.no_ktp_pasangan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">Tgl. Lahir</label>
                            <input type="date" name="form_data[tgl_lahir_pasangan]"
                                value="{{ old('form_data.tgl_lahir_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div x-data="{ seumurHidup: {{ old('form_data.ktp_seumur_hidup_pasangan') ? 'true' : 'false' }} }">
                            <label class="text-sm">KTP Berlaku s/d</label>
                            <input type="date" name="form_data[ktp_berlaku_pasangan]"
                                :value="seumurHidup ? '' : '{{ old('form_data.ktp_berlaku_pasangan') }}'"
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
                    <h3 class="font-semibold text-gray-800 border-b pb-2">Data Kontak Darurat <span class="text-xs font-normal text-gray-500">(tidak serumah)</span></h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-sm">Nama</label>
                            <input type="text" name="form_data[nama_darurat]"
                                value="{{ old('form_data.nama_darurat') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Telp / HP</label>
                            <input type="text" name="form_data[telp_darurat]"
                                value="{{ old('form_data.telp_darurat') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Alamat</label>
                        <input type="text" name="form_data[alamat_darurat]"
                            value="{{ old('form_data.alamat_darurat') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm">RT</label>
                            <input type="text" name="form_data[rt_darurat]" value="{{ old('form_data.rt_darurat') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">RW</label>
                            <input type="text" name="form_data[rw_darurat]" value="{{ old('form_data.rw_darurat') }}"
                                class="w-full border rounded px-3 py-2" maxlength="5">
                        </div>
                        <div>
                            <label class="text-sm">Kode Pos</label>
                            <input type="text" name="form_data[kode_pos_darurat]"
                                value="{{ old('form_data.kode_pos_darurat') }}"
                                class="w-full border rounded px-3 py-2" maxlength="10">
                        </div>
                        <div>
                            <label class="text-sm">Hubungan</label>
                            <input type="text" name="form_data[hubungan_darurat]"
                                value="{{ old('form_data.hubungan_darurat') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Desa / Kelurahan</label>
                            <input type="text" name="form_data[kelurahan_darurat]"
                                value="{{ old('form_data.kelurahan_darurat') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kecamatan</label>
                            <input type="text" name="form_data[kecamatan_darurat]"
                                value="{{ old('form_data.kecamatan_darurat') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kota</label>
                            <input type="text" name="form_data[kota_darurat]"
                                value="{{ old('form_data.kota_darurat') }}"
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
                                value="{{ old('form_data.pekerjaan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Perusahaan / Instansi</label>
                            <input type="text" name="form_data[perusahaan]"
                                value="{{ old('form_data.perusahaan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Alamat Perusahaan</label>
                        <input type="text" name="form_data[alamat_perusahaan]"
                            value="{{ old('form_data.alamat_perusahaan') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm">Kota</label>
                            <input type="text" name="form_data[kota_perusahaan]"
                                value="{{ old('form_data.kota_perusahaan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kode Pos</label>
                            <input type="text" name="form_data[kode_pos_perusahaan]"
                                value="{{ old('form_data.kode_pos_perusahaan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="10">
                        </div>
                        <div>
                            <label class="text-sm">Telp.</label>
                            <input type="text" name="form_data[telp_perusahaan]"
                                value="{{ old('form_data.telp_perusahaan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">Fax</label>
                            <input type="text" name="form_data[fax_perusahaan]"
                                value="{{ old('form_data.fax_perusahaan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Jenis Usaha</label>
                            <input type="text" name="form_data[jenis_usaha]"
                                value="{{ old('form_data.jenis_usaha') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Jabatan</label>
                            <input type="text" name="form_data[jabatan]"
                                value="{{ old('form_data.jabatan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Total Masa Kerja</label>
                            <input type="text" name="form_data[masa_kerja]"
                                value="{{ old('form_data.masa_kerja') }}"
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
                                value="{{ old('form_data.pekerjaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Perusahaan / Instansi</label>
                            <input type="text" name="form_data[perusahaan_pasangan]"
                                value="{{ old('form_data.perusahaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm">Alamat Perusahaan</label>
                        <input type="text" name="form_data[alamat_perusahaan_pasangan]"
                            value="{{ old('form_data.alamat_perusahaan_pasangan') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm">Kota</label>
                            <input type="text" name="form_data[kota_perusahaan_pasangan]"
                                value="{{ old('form_data.kota_perusahaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Kode Pos</label>
                            <input type="text" name="form_data[kode_pos_perusahaan_pasangan]"
                                value="{{ old('form_data.kode_pos_perusahaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="10">
                        </div>
                        <div>
                            <label class="text-sm">Telp.</label>
                            <input type="text" name="form_data[telp_perusahaan_pasangan]"
                                value="{{ old('form_data.telp_perusahaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                        <div>
                            <label class="text-sm">Fax</label>
                            <input type="text" name="form_data[fax_perusahaan_pasangan]"
                                value="{{ old('form_data.fax_perusahaan_pasangan') }}"
                                class="w-full border rounded px-3 py-2" maxlength="20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm">Jenis Usaha</label>
                            <input type="text" name="form_data[jenis_usaha_pasangan]"
                                value="{{ old('form_data.jenis_usaha_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Jabatan</label>
                            <input type="text" name="form_data[jabatan_pasangan]"
                                value="{{ old('form_data.jabatan_pasangan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Total Masa Kerja</label>
                            <input type="text" name="form_data[masa_kerja_pasangan]"
                                value="{{ old('form_data.masa_kerja_pasangan') }}"
                                class="w-full border rounded px-3 py-2" placeholder="cth: 5 Tahun 3 Bulan">
                        </div>
                    </div>
                </div>

                {{-- ===== DATA AGUNAN ===== --}}
                <div class="bg-white p-6 rounded shadow space-y-4">
                    <h3 class="font-semibold text-gray-800 border-b pb-2">Data Agunan</h3>

                    {{-- Tanah & Bangunan --}}
                    <div class="space-y-3">
                        <div class="text-sm font-medium text-gray-600">1. Tanah dan Bangunan</div>

                        <div>
                            <label class="text-sm">Lokasi Agunan</label>
                            <input type="text" name="form_data[lokasi_agunan]"
                                value="{{ old('form_data.lokasi_agunan') }}"
                                class="w-full border rounded px-3 py-2">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm">Status</label>
                                <select name="form_data[status_agunan]" class="w-full border rounded px-3 py-2">
                                    <option value="">Pilih</option>
                                    @foreach(['SHM','SHGB','SHP','HGB','Girik/Letter C','Lainnya'] as $s)
                                        <option value="{{ $s }}" @selected(old('form_data.status_agunan') == $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-sm">Atas Nama</label>
                                <input type="text" name="form_data[atas_nama_agunan]"
                                    value="{{ old('form_data.atas_nama_agunan') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Nilai / Harga (NJOP) Rp</label>
                                <input type="text" data-money name="form_data[nilai_agunan]"
                                    value="{{ old('form_data.nilai_agunan') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-sm">No. Hak</label>
                                <input type="text" name="form_data[no_hak]"
                                    value="{{ old('form_data.no_hak') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Luas Tanah (m²)</label>
                                <input type="text" name="form_data[luas_tanah]"
                                    value="{{ old('form_data.luas_tanah') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Luas Bangunan (m²)</label>
                                <input type="text" name="form_data[luas_bangunan]"
                                    value="{{ old('form_data.luas_bangunan') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">No. IMB</label>
                                <input type="text" name="form_data[no_imb]"
                                    value="{{ old('form_data.no_imb') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                    </div>

                    {{-- Kendaraan & BPKB --}}
                    <div class="space-y-3 border-t pt-3">
                        <div class="text-sm font-medium text-gray-600">2. Kendaraan dan BPKB</div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm">Jenis Kendaraan</label>
                                <input type="text" name="form_data[jenis_kendaraan]"
                                    value="{{ old('form_data.jenis_kendaraan') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Tahun</label>
                                <input type="number" name="form_data[tahun_kendaraan]"
                                    value="{{ old('form_data.tahun_kendaraan') }}"
                                    class="w-full border rounded px-3 py-2" min="1970" max="{{ date('Y') + 1 }}">
                            </div>
                            <div>
                                <label class="text-sm">Warna</label>
                                <input type="text" name="form_data[warna_kendaraan]"
                                    value="{{ old('form_data.warna_kendaraan') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="text-sm">No. BPKB</label>
                                <input type="text" name="form_data[no_bpkb]"
                                    value="{{ old('form_data.no_bpkb') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Atas Nama</label>
                                <input type="text" name="form_data[atas_nama_bpkb]"
                                    value="{{ old('form_data.atas_nama_bpkb') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">No. Mesin</label>
                                <input type="text" name="form_data[no_mesin]"
                                    value="{{ old('form_data.no_mesin') }}"
                                    class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">No. Rangka</label>
                                <input type="text" name="form_data[no_rangka]"
                                    value="{{ old('form_data.no_rangka') }}"
                                    class="w-full border rounded px-3 py-2">
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
                                    value="{{ old('form_data.penghasilan_pemohon') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                            <div>
                                <label class="text-sm">2. Penghasilan Istri / Suami (Rp)</label>
                                <input type="text" data-money name="form_data[penghasilan_pasangan]"
                                    value="{{ old('form_data.penghasilan_pasangan') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                            <div>
                                <label class="text-sm">3. Penghasilan Lain-lain (Rp)</label>
                                <input type="text" data-money name="form_data[penghasilan_lain]"
                                    value="{{ old('form_data.penghasilan_lain') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="text-sm font-medium text-gray-600">Sumber Pengeluaran / Bulan</div>
                            <div>
                                <label class="text-sm">1. Biaya Sewa (Rp)</label>
                                <input type="text" data-money name="form_data[biaya_sewa]"
                                    value="{{ old('form_data.biaya_sewa') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                            <div>
                                <label class="text-sm">2. Biaya Hidup (Rp)</label>
                                <input type="text" data-money name="form_data[biaya_hidup]"
                                    value="{{ old('form_data.biaya_hidup') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                            <div>
                                <label class="text-sm">3. Angsuran Pinjaman Lain (Rp)</label>
                                <input type="text" data-money name="form_data[angsuran_lain]"
                                    value="{{ old('form_data.angsuran_lain') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                            <div>
                                <label class="text-sm">4. Biaya Lain-lain (Rp)</label>
                                <input type="text" data-money name="form_data[biaya_lain]"
                                    value="{{ old('form_data.biaya_lain') }}"
                                    class="w-full border rounded px-3 py-2" placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== DOKUMEN ===== --}}
                <div class="bg-white p-6 rounded shadow space-y-4">
                    <h3 class="font-semibold text-gray-800 border-b pb-2">Dokumen</h3>
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
                    <div>
                        <label class="text-sm">Dokumen Lainnya (bisa lebih dari satu)</label>
                        <input type="file" name="doc_others[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                            class="w-full border rounded px-3 py-2 text-sm">
                        @error('doc_others.*') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="text-xs text-gray-400">Format: JPG, PNG, PDF. Maks 5 MB per file.</div>
                </div>

                {{-- ===== NOTARIS (optional) ===== --}}
                <details class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <summary class="px-5 py-4 cursor-pointer flex items-center justify-between text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <span>Notaris <span class="text-xs text-gray-400 font-normal">(opsional)</span></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-5 pb-5 pt-2 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Nama Notaris</label>
                            <input type="text" name="notaris_name" value="{{ old('notaris_name') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: H. Budi Santoso, SH">
                            @error('notaris_name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">No. Akta</label>
                            <input type="text" name="notaris_deed_number" value="{{ old('notaris_deed_number') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: 001/2026">
                            @error('notaris_deed_number') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Tanggal Akta</label>
                            <input type="date" name="notaris_deed_date" value="{{ old('notaris_deed_date') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            @error('notaris_deed_date') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Kota / Kabupaten</label>
                            <input type="text" name="notaris_city" value="{{ old('notaris_city') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300"
                                   placeholder="cth: Surabaya">
                            @error('notaris_city') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </details>

                {{-- ===== ACTIONS ===== --}}
                <div class="flex gap-3">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded">Simpan Pengajuan</button>
                    <a href="{{ route('admin.loans.applications.index') }}"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded">Batal</a>
                </div>

            </form>
    <script>
        (function() {
            const sel = document.getElementById('loan_product_id');
            const hint = document.getElementById('loan_product_hint');
            const methodHint = document.getElementById('interest_method_hint');
            const tenor = document.getElementById('tenor_months');
            const rate = document.getElementById('interest_monthly_percent');

            let rateTouched = false;

            rate.addEventListener('input', () => rateTouched = true);

            function applyProduct(force = false) {
                const opt = sel.options[sel.selectedIndex];
                if (!opt || !opt.value) { hint.textContent = ''; return; }

                const maxTenor = parseInt(opt.dataset.maxTenor || '0', 10);
                const rateBp = parseInt(opt.dataset.rateBp || '0', 10);
                const method = opt.dataset.method || 'FLAT';
                const ratePercent = (rateBp / 100).toFixed(2);

                const methodLabel = method === 'EFEKTIF' ? 'Efektif (menurun)' : 'Flat (tetap)';
                hint.textContent =
                    `Default: bunga ${ratePercent}%/bln, max tenor ${maxTenor} bln. Biaya admin diinput manual.`;
                if (methodHint) methodHint.textContent = `Metode: ${methodLabel}. Auto dari produk, masih bisa diubah.`;

                if (force || !rateTouched) rate.value = ratePercent;

                if (maxTenor > 0) {
                    tenor.max = String(maxTenor);
                    if (parseInt(tenor.value || '0', 10) > maxTenor) tenor.value = String(maxTenor);
                }
            }

            sel.addEventListener('change', () => applyProduct(true));
            applyProduct(false);
        })();
    </script>
</x-admin-layout>
