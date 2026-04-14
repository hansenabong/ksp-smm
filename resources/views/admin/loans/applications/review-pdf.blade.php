<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Review Pengajuan #{{ $application->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 9.5pt;
            color: #111;
            line-height: 1.4;
        }
        .page { padding: 1.5cm 1.6cm; }

        .kop { border-bottom: 2px solid #111; padding-bottom: 6pt; margin-bottom: 10pt; }
        .kop-name { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
        .kop-sub { font-size: 8pt; color: #555; margin-top: 1pt; }

        h1.title {
            font-size: 12pt;
            text-align: center;
            text-transform: uppercase;
            margin: 8pt 0 2pt 0;
            letter-spacing: 1px;
        }
        .subtitle { text-align: center; font-size: 8.5pt; color: #555; margin-bottom: 10pt; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
        .meta-table td { padding: 2pt 4pt; font-size: 9pt; }
        .meta-table td:nth-child(1) { width: 18%; color: #555; }
        .meta-table td:nth-child(2) { width: 32%; font-weight: bold; }
        .meta-table td:nth-child(3) { width: 18%; color: #555; }
        .meta-table td:nth-child(4) { width: 32%; font-weight: bold; }

        .section-title {
            background: #111;
            color: #fff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 4pt 8pt;
            margin-top: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4pt;
        }
        table.data td {
            padding: 3pt 6pt;
            border: 0.5pt solid #bbb;
            vertical-align: top;
            font-size: 9pt;
        }
        table.data td.label {
            width: 25%;
            background: #f3f3f3;
            color: #444;
            font-weight: 600;
        }
        table.data td.value { width: 25%; }

        .empty { color: #aaa; font-style: italic; }

        .signature-wrap { margin-top: 18pt; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { width: 33.33%; vertical-align: top; padding: 0 8pt; text-align: center; font-size: 9pt; }
        .signature-box { margin-top: 50pt; border-top: 1px solid #000; padding-top: 3pt; font-weight: bold; }

        .footer {
            margin-top: 14pt;
            font-size: 7.5pt;
            color: #777;
            border-top: 0.5pt solid #ccc;
            padding-top: 4pt;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $fd = $application->form_data ?? [];
    $member = $application->member;

    // Helper: render value or em-dash
    $val = function ($v, $fmt = null) {
        if ($v === null || $v === '' || $v === 0 || $v === '0') {
            return '<span class="empty">—</span>';
        }
        if ($fmt === 'rp')   return 'Rp ' . number_format((int)$v, 0, ',', '.');
        if ($fmt === 'date') {
            try { return \Carbon\Carbon::parse($v)->format('d/m/Y'); } catch (\Throwable $e) { return e($v); }
        }
        return e($v);
    };

    // Loan calculation snapshot
    $monthlyInterest = $application->principal * ($application->interest_monthly_percent / 100);
    $monthlyPokok    = $application->tenor_months > 0 ? $application->principal / $application->tenor_months : 0;
    $estAngsuran     = $monthlyPokok + $monthlyInterest + $application->admin_monthly + $application->insurance_monthly;

    // Pendapatan & pengeluaran totals
    $totalPendapatan = ($fd['penghasilan_pemohon'] ?? 0) + ($fd['penghasilan_pasangan'] ?? 0) + ($fd['penghasilan_lain'] ?? 0);
    $totalPengeluaran = ($fd['biaya_sewa'] ?? 0) + ($fd['biaya_hidup'] ?? 0) + ($fd['angsuran_lain'] ?? 0) + ($fd['biaya_lain'] ?? 0);
@endphp

<div class="page">

    {{-- ===== KOP ===== --}}
    <div class="kop">
        <div class="kop-name">KSP — Koperasi Simpan Pinjam</div>
        <div class="kop-sub">Lembar Review Pengajuan Pinjaman (Internal)</div>
    </div>

    <h1 class="title">Review Pengajuan Pinjaman</h1>
    <div class="subtitle">Pengajuan #{{ $application->id }} &nbsp;·&nbsp; Status: <strong>{{ $application->status }}</strong></div>

    <table class="meta-table">
        <tr>
            <td>Tgl. Pengajuan</td>
            <td>{!! $val(optional($application->trx_date)->format('d/m/Y')) !!}</td>
            <td>Dibuat Oleh</td>
            <td>{!! $val($application->creator?->name) !!}</td>
        </tr>
        <tr>
            <td>No. Anggota</td>
            <td>{!! $val($member?->member_no) !!}</td>
            <td>Tgl. Cetak Review</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    {{-- ===== RINGKASAN PINJAMAN ===== --}}
    <div class="section-title">A. Ringkasan Pinjaman</div>
    <table class="data">
        <tr>
            <td class="label">Produk Pinjaman</td>
            <td class="value">{!! $val($application->loanProduct?->name) !!}</td>
            <td class="label">Metode</td>
            <td class="value">{!! $val($application->method) !!}</td>
        </tr>
        <tr>
            <td class="label">Pokok Pinjaman</td>
            <td class="value">{!! $val($application->principal, 'rp') !!}</td>
            <td class="label">Tenor</td>
            <td class="value">{{ $application->tenor_months }} bulan</td>
        </tr>
        <tr>
            <td class="label">Bunga Flat / bulan</td>
            <td class="value">{{ $application->interest_monthly_percent }}%</td>
            <td class="label">Biaya Admin / bulan</td>
            <td class="value">{!! $val($application->admin_monthly, 'rp') !!}</td>
        </tr>
        <tr>
            <td class="label">Asuransi / bulan</td>
            <td class="value">{!! $val($application->insurance_monthly, 'rp') !!}</td>
            <td class="label">Estimasi Angsuran / bulan</td>
            <td class="value"><strong>{{ 'Rp ' . number_format($estAngsuran, 0, ',', '.') }}</strong></td>
        </tr>
        @if($application->notes)
        <tr>
            <td class="label">Catatan</td>
            <td class="value" colspan="3">{{ $application->notes }}</td>
        </tr>
        @endif
    </table>

    {{-- ===== DATA PEMOHON ===== --}}
    <div class="section-title">B. Data Pemohon</div>
    <table class="data">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="value">{!! $val($fd['nama_lengkap'] ?? $member?->name) !!}</td>
            <td class="label">Jenis Kelamin</td>
            <td class="value">{!! $val(($fd['kelamin'] ?? null) === 'L' ? 'Laki-laki' : (($fd['kelamin'] ?? null) === 'P' ? 'Perempuan' : null)) !!}</td>
        </tr>
        <tr>
            <td class="label">No. KTP</td>
            <td class="value">{!! $val($fd['no_ktp'] ?? null) !!}</td>
            <td class="label">Berlaku Hingga</td>
            <td class="value">
                @if(!empty($fd['ktp_seumur_hidup']))
                    <strong>Seumur Hidup</strong> <span style="color:#666">(e-KTP)</span>
                @else
                    {!! $val($fd['ktp_berlaku'] ?? null, 'date') !!}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Tgl. Lahir</td>
            <td class="value">{!! $val($fd['tgl_lahir'] ?? null, 'date') !!}</td>
            <td class="label">Status Perkawinan</td>
            <td class="value">{!! $val($fd['status_perkawinan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Pendidikan</td>
            <td class="value">{!! $val($fd['pendidikan'] ?? null) !!}</td>
            <td class="label">Nama Ibu Kandung</td>
            <td class="value">{!! $val($fd['nama_ibu_kandung'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td class="value" colspan="3">{!! $val($fd['alamat'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">RT / RW</td>
            <td class="value">{!! $val(($fd['rt'] ?? '-') . ' / ' . ($fd['rw'] ?? '-')) !!}</td>
            <td class="label">Kota / Kode Pos</td>
            <td class="value">{!! $val(($fd['kota'] ?? '') . ($fd['kode_pos'] ?? '' ? ' / ' . ($fd['kode_pos'] ?? '') : '')) !!}</td>
        </tr>
        <tr>
            <td class="label">Telp Rumah / HP</td>
            <td class="value">{!! $val(($fd['telp_rumah'] ?? '-') . ' / ' . ($fd['hp'] ?? '-')) !!}</td>
            <td class="label">Kepemilikan Rumah</td>
            <td class="value">{!! $val($fd['kepemilikan_rumah'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Lama Menempati</td>
            <td class="value">{{ ($fd['lama_menempati_thn'] ?? 0) }} thn {{ ($fd['lama_menempati_bln'] ?? 0) }} bln</td>
            <td class="label">Jumlah Tanggungan</td>
            <td class="value">{!! $val($fd['jumlah_tanggungan'] ?? null) !!}</td>
        </tr>
    </table>

    {{-- ===== PASANGAN ===== --}}
    @if(!empty($fd['nama_pasangan']) || !empty($fd['no_ktp_pasangan']))
    <div class="section-title">C. Data Pasangan</div>
    <table class="data">
        <tr>
            <td class="label">Nama Pasangan</td>
            <td class="value">{!! $val($fd['nama_pasangan'] ?? null) !!}</td>
            <td class="label">No. KTP</td>
            <td class="value">{!! $val($fd['no_ktp_pasangan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Tgl. Lahir</td>
            <td class="value">{!! $val($fd['tgl_lahir_pasangan'] ?? null, 'date') !!}</td>
            <td class="label">Berlaku Hingga</td>
            <td class="value">
                @if(!empty($fd['ktp_seumur_hidup_pasangan']))
                    <strong>Seumur Hidup</strong> <span style="color:#666">(e-KTP)</span>
                @else
                    {!! $val($fd['ktp_berlaku_pasangan'] ?? null, 'date') !!}
                @endif
            </td>
        </tr>
    </table>
    @endif

    {{-- ===== KONTAK DARURAT ===== --}}
    @if(!empty($fd['nama_darurat']))
    <div class="section-title">D. Kontak Darurat</div>
    <table class="data">
        <tr>
            <td class="label">Nama</td>
            <td class="value">{!! $val($fd['nama_darurat'] ?? null) !!}</td>
            <td class="label">Hubungan</td>
            <td class="value">{!! $val($fd['hubungan_darurat'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Telp.</td>
            <td class="value">{!! $val($fd['telp_darurat'] ?? null) !!}</td>
            <td class="label">RT / RW</td>
            <td class="value">{!! $val(($fd['rt_darurat'] ?? '-') . ' / ' . ($fd['rw_darurat'] ?? '-')) !!}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td class="value" colspan="3">{!! $val($fd['alamat_darurat'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Kelurahan / Kecamatan</td>
            <td class="value">{!! $val(($fd['kelurahan_darurat'] ?? '-') . ' / ' . ($fd['kecamatan_darurat'] ?? '-')) !!}</td>
            <td class="label">Kota / Kode Pos</td>
            <td class="value">{!! $val(($fd['kota_darurat'] ?? '-') . ' / ' . ($fd['kode_pos_darurat'] ?? '-')) !!}</td>
        </tr>
    </table>
    @endif

    {{-- ===== PEKERJAAN ===== --}}
    @if(!empty($fd['pekerjaan']) || !empty($fd['perusahaan']))
    <div class="section-title">E. Data Pekerjaan Pemohon</div>
    <table class="data">
        <tr>
            <td class="label">Pekerjaan</td>
            <td class="value">{!! $val($fd['pekerjaan'] ?? null) !!}</td>
            <td class="label">Jabatan</td>
            <td class="value">{!! $val($fd['jabatan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Perusahaan</td>
            <td class="value">{!! $val($fd['perusahaan'] ?? null) !!}</td>
            <td class="label">Jenis Usaha</td>
            <td class="value">{!! $val($fd['jenis_usaha'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Alamat Perusahaan</td>
            <td class="value" colspan="3">{!! $val($fd['alamat_perusahaan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Kota / Kode Pos</td>
            <td class="value">{!! $val(($fd['kota_perusahaan'] ?? '-') . ' / ' . ($fd['kode_pos_perusahaan'] ?? '-')) !!}</td>
            <td class="label">Telp / Fax</td>
            <td class="value">{!! $val(($fd['telp_perusahaan'] ?? '-') . ' / ' . ($fd['fax_perusahaan'] ?? '-')) !!}</td>
        </tr>
        <tr>
            <td class="label">Masa Kerja</td>
            <td class="value" colspan="3">{!! $val($fd['masa_kerja'] ?? null) !!}</td>
        </tr>
    </table>
    @endif

    @if(!empty($fd['pekerjaan_pasangan']) || !empty($fd['perusahaan_pasangan']))
    <div class="section-title">F. Data Pekerjaan Pasangan</div>
    <table class="data">
        <tr>
            <td class="label">Pekerjaan</td>
            <td class="value">{!! $val($fd['pekerjaan_pasangan'] ?? null) !!}</td>
            <td class="label">Jabatan</td>
            <td class="value">{!! $val($fd['jabatan_pasangan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Perusahaan</td>
            <td class="value">{!! $val($fd['perusahaan_pasangan'] ?? null) !!}</td>
            <td class="label">Jenis Usaha</td>
            <td class="value">{!! $val($fd['jenis_usaha_pasangan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Alamat Perusahaan</td>
            <td class="value" colspan="3">{!! $val($fd['alamat_perusahaan_pasangan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Kota / Kode Pos</td>
            <td class="value">{!! $val(($fd['kota_perusahaan_pasangan'] ?? '-') . ' / ' . ($fd['kode_pos_perusahaan_pasangan'] ?? '-')) !!}</td>
            <td class="label">Telp / Fax</td>
            <td class="value">{!! $val(($fd['telp_perusahaan_pasangan'] ?? '-') . ' / ' . ($fd['fax_perusahaan_pasangan'] ?? '-')) !!}</td>
        </tr>
        <tr>
            <td class="label">Masa Kerja</td>
            <td class="value" colspan="3">{!! $val($fd['masa_kerja_pasangan'] ?? null) !!}</td>
        </tr>
    </table>
    @endif

    {{-- ===== AGUNAN ===== --}}
    <div class="section-title">G. Data Agunan</div>
    @if(!empty($fd['lokasi_agunan']) || !empty($fd['no_hak']))
    <table class="data">
        <tr>
            <td class="label" colspan="4" style="background:#e6e6e6;">1. Tanah dan Bangunan</td>
        </tr>
        <tr>
            <td class="label">Lokasi Agunan</td>
            <td class="value" colspan="3">{!! $val($fd['lokasi_agunan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">{!! $val($fd['status_agunan'] ?? null) !!}</td>
            <td class="label">Atas Nama</td>
            <td class="value">{!! $val($fd['atas_nama_agunan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Nilai / NJOP</td>
            <td class="value">{!! $val($fd['nilai_agunan'] ?? null, 'rp') !!}</td>
            <td class="label">No. Hak</td>
            <td class="value">{!! $val($fd['no_hak'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Luas Tanah (m²)</td>
            <td class="value">{!! $val($fd['luas_tanah'] ?? null) !!}</td>
            <td class="label">Luas Bangunan (m²)</td>
            <td class="value">{!! $val($fd['luas_bangunan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">No. IMB</td>
            <td class="value" colspan="3">{!! $val($fd['no_imb'] ?? null) !!}</td>
        </tr>
    </table>
    @endif
    @if(!empty($fd['jenis_kendaraan']) || !empty($fd['no_bpkb']))
    <table class="data">
        <tr>
            <td class="label" colspan="4" style="background:#e6e6e6;">2. Kendaraan dan BPKB</td>
        </tr>
        <tr>
            <td class="label">Jenis Kendaraan</td>
            <td class="value">{!! $val($fd['jenis_kendaraan'] ?? null) !!}</td>
            <td class="label">Tahun</td>
            <td class="value">{!! $val($fd['tahun_kendaraan'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Warna</td>
            <td class="value">{!! $val($fd['warna_kendaraan'] ?? null) !!}</td>
            <td class="label">No. BPKB</td>
            <td class="value">{!! $val($fd['no_bpkb'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">Atas Nama (BPKB)</td>
            <td class="value">{!! $val($fd['atas_nama_bpkb'] ?? null) !!}</td>
            <td class="label">No. Mesin</td>
            <td class="value">{!! $val($fd['no_mesin'] ?? null) !!}</td>
        </tr>
        <tr>
            <td class="label">No. Rangka</td>
            <td class="value" colspan="3">{!! $val($fd['no_rangka'] ?? null) !!}</td>
        </tr>
    </table>
    @endif
    @if(empty($fd['lokasi_agunan']) && empty($fd['no_hak']) && empty($fd['jenis_kendaraan']) && empty($fd['no_bpkb']))
    <table class="data"><tr><td colspan="4" class="empty" style="text-align:center">Tidak ada data agunan.</td></tr></table>
    @endif

    {{-- ===== PENDAPATAN & PENGELUARAN ===== --}}
    <div class="section-title">H. Pendapatan & Pengeluaran per Bulan</div>
    <table class="data">
        <tr>
            <td class="label">Penghasilan Pemohon</td>
            <td class="value">{!! $val($fd['penghasilan_pemohon'] ?? null, 'rp') !!}</td>
            <td class="label">Biaya Sewa</td>
            <td class="value">{!! $val($fd['biaya_sewa'] ?? null, 'rp') !!}</td>
        </tr>
        <tr>
            <td class="label">Penghasilan Pasangan</td>
            <td class="value">{!! $val($fd['penghasilan_pasangan'] ?? null, 'rp') !!}</td>
            <td class="label">Biaya Hidup</td>
            <td class="value">{!! $val($fd['biaya_hidup'] ?? null, 'rp') !!}</td>
        </tr>
        <tr>
            <td class="label">Penghasilan Lain-lain</td>
            <td class="value">{!! $val($fd['penghasilan_lain'] ?? null, 'rp') !!}</td>
            <td class="label">Angsuran Pinjaman Lain</td>
            <td class="value">{!! $val($fd['angsuran_lain'] ?? null, 'rp') !!}</td>
        </tr>
        <tr>
            <td class="label">Tujuan Pembiayaan</td>
            <td class="value">{!! $val($fd['tujuan_pembiayaan'] ?? null) !!}</td>
            <td class="label">Biaya Lain-lain</td>
            <td class="value">{!! $val($fd['biaya_lain'] ?? null, 'rp') !!}</td>
        </tr>
        <tr>
            <td class="label">Total Pendapatan</td>
            <td class="value"><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
            <td class="label">Total Pengeluaran</td>
            <td class="value"><strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Net (Pendapatan − Pengeluaran)</td>
            <td class="value" colspan="3"><strong>Rp {{ number_format($totalPendapatan - $totalPengeluaran, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    {{-- ===== DOKUMEN ===== --}}
    @if($application->files && $application->files->count() > 0)
    <div class="section-title">I. Dokumen Terlampir</div>
    <table class="data">
        <tr>
            <td class="label" style="width:8%">No</td>
            <td class="label" style="width:25%">Kategori</td>
            <td class="label" colspan="2">Nama File</td>
        </tr>
        @foreach($application->files as $i => $f)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $f->category }}</td>
            <td colspan="2">{{ $f->original_name }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-wrap">
        <table class="signature-table">
            <tr>
                <td>
                    Dibuat oleh,
                    <div class="signature-box">{{ $application->creator?->name ?? '_____________' }}</div>
                    <div style="font-size:8pt;color:#666">Admin Data</div>
                </td>
                <td>
                    Diperiksa oleh,
                    <div class="signature-box">_____________</div>
                    <div style="font-size:8pt;color:#666">Pengawas / Legal</div>
                </td>
                <td>
                    Disetujui oleh,
                    <div class="signature-box">{{ $application->approver?->name ?? '_____________' }}</div>
                    <div style="font-size:8pt;color:#666">{{ $application->approver ? 'Tanggal: ' . optional($application->approved_at)->format('d/m/Y') : 'Pimpinan' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Lembar Review Pengajuan Pinjaman — dokumen internal KSP. Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()?->name ?? '-' }}.
    </div>

</div>
</body>
</html>
