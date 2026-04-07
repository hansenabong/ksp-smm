<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penawaran Pembiayaan {{ $nomorSurat }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.6;
        }
        .page { padding: 2cm 2.5cm 2cm 2.5cm; }

        /* Letterhead */
        .kop { border-bottom: 3px double #000; padding-bottom: 8pt; margin-bottom: 14pt; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo td { vertical-align: middle; }
        .kop-name { font-size: 15pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-sub  { font-size: 9pt; color: #444; margin-top: 2pt; }

        /* Surat header */
        .surat-info { margin-bottom: 14pt; }
        .surat-info table { border-collapse: collapse; }
        .surat-info td { padding: 1pt 0; vertical-align: top; }
        .surat-info td:first-child { width: 130pt; }
        .surat-info td:nth-child(2) { width: 14pt; text-align: center; }

        /* Recipient */
        .kepada { margin-bottom: 14pt; }

        /* Body */
        .perihal-label { font-weight: bold; text-decoration: underline; margin-bottom: 10pt; }
        p { text-align: justify; margin-bottom: 8pt; }

        /* Detail table */
        .detail-table { width: 100%; border-collapse: collapse; margin: 10pt 0; }
        .detail-table td { padding: 3pt 5pt; vertical-align: top; border: 1px solid #aaa; font-size: 10.5pt; }
        .detail-table td:first-child { width: 38%; background: #f9f9f9; font-weight: bold; }
        .detail-table td:nth-child(2) { width: 4%; text-align: center; }
        .detail-table .section-header td { background: #000; color: #fff; font-weight: bold; text-align: center; }

        /* Syarat list */
        .syarat-list { margin: 6pt 0 6pt 16pt; }
        .syarat-list li { margin-bottom: 3pt; text-align: justify; }

        /* Signature */
        .signature-wrap { margin-top: 24pt; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { width: 50%; vertical-align: top; padding: 0 10pt; text-align: center; }
        .signature-box { margin-top: 56pt; border-top: 1px solid #000; padding-top: 4pt; font-weight: bold; }

        .footer { margin-top: 16pt; font-size: 8.5pt; color: #555; border-top: 1px solid #bbb; padding-top: 6pt; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    {{-- ===== KETERANGAN PENGHITUNGAN ===== --}}
    @php
        $fd      = $application->form_data ?? [];
        $member  = $application->member;

        $principal  = $application->principal;
        $tenor      = $application->tenor_months;
        $bungaBp    = $application->interest_rate_monthly_bp ?? ($application->interest_monthly_percent * 100);
        $bungaPct   = $bungaBp / 100;   // % per bulan
        $method     = $application->method ?? 'FLAT';
        $adminFee   = $application->admin_fee ?? 0;
        $insurance  = ($application->insurance_monthly ?? 0) * $tenor;

        // Angsuran — pakai dari Loan jika sudah ada, fallback hitung manual
        if ($loan && $loan->monthly_installment > 0) {
            $angsuran = $loan->monthly_installment;
        } elseif (strtoupper($method) === 'FLAT') {
            $totalBunga = $principal * ($bungaPct / 100) * $tenor;
            $angsuran   = round(($principal + $totalBunga) / $tenor);
        } else {
            // Annuity/efektif
            $r = $bungaPct / 100;
            $angsuran = $r > 0
                ? round($principal * $r * pow(1+$r, $tenor) / (pow(1+$r, $tenor) - 1))
                : round($principal / $tenor);
        }

        $totalAngsuran = $angsuran * $tenor;

        // Provisi
        $provisiPct = ($application->loanProduct?->provision_rate_percent ?? 0);
        $provisi    = round($principal * $provisiPct / 100);

        // Agunan
        $jaminanText = '';
        if (!empty($fd['no_hak'])) {
            $jaminanText = 'Sertipikat ' . ($fd['jenis_hak'] ?? 'SHM') . ' No. ' . $fd['no_hak'];
            if (!empty($fd['lokasi_agunan'])) $jaminanText .= ', ' . $fd['lokasi_agunan'];
        } elseif (!empty($fd['jenis_kendaraan'])) {
            $jaminanText = $fd['jenis_kendaraan'];
            if (!empty($fd['merk_kendaraan'])) $jaminanText .= ' ' . $fd['merk_kendaraan'];
            if (!empty($fd['tahun_kendaraan'])) $jaminanText .= ' Th. ' . $fd['tahun_kendaraan'];
            if (!empty($fd['no_polisi'])) $jaminanText .= ' (' . $fd['no_polisi'] . ')';
        } elseif ($application->collaterals->isNotEmpty()) {
            $jaminanText = $application->collaterals->pluck('description')->implode('; ');
        } else {
            $jaminanText = '-';
        }

        $tglSurat  = now()->format('d') . ' ' . \Carbon\Carbon::now()->translatedFormat('F') . ' ' . now()->format('Y');
        $berlakuHingga = now()->addDays(14)->format('d F Y');
        $approverName = $application->approver?->name ?? 'Pimpinan';
    @endphp

    {{-- ===== KOP SURAT ===== --}}
    <div class="kop">
        <table class="kop-table kop-logo"><tr>
            <td style="width:60pt; padding-right:10pt;">
                <img src="{{ public_path('images/logo-ksp.jpeg') }}" alt="KSP SMM" style="width:54pt; height:54pt; object-fit:contain;">
            </td>
            <td>
                <div class="kop-name">KOPERASI SIMPAN PINJAM SARANA MITRA MANDIRI</div>
                <div class="kop-sub">Koperasi Simpan Pinjam &nbsp;|&nbsp; Jakarta</div>
            </td>
        </tr></table>
    </div>

    {{-- ===== INFO SURAT ===== --}}
    <div class="surat-info">
        <table>
            <tr><td>Nomor</td><td>:</td><td>{{ $nomorSurat }}</td></tr>
            <tr><td>Tanggal</td><td>:</td><td>{{ $tglSurat }}</td></tr>
            <tr><td>Lampiran</td><td>:</td><td>—</td></tr>
            <tr><td>Perihal</td><td>:</td><td><strong>Penawaran Pembiayaan Konsumen</strong></td></tr>
        </table>
    </div>

    {{-- ===== KEPADA ===== --}}
    <div class="kepada">
        <div>Kepada Yth.</div>
        <div><strong>{{ $member->name }}</strong></div>
        <div>{{ $member->address ?? '-' }}</div>
        <div style="margin-top:4pt;font-style:italic;">di Tempat</div>
    </div>

    {{-- ===== SALAM PEMBUKA ===== --}}
    <p>Dengan hormat,</p>
    <p>
        Sehubungan dengan permohonan pembiayaan yang Saudara/i ajukan kepada Koperasi Simpan Pinjam SMM,
        dengan ini kami menyampaikan penawaran pembiayaan dengan ketentuan sebagai berikut:
    </p>

    {{-- ===== TABEL DETAIL PEMBIAYAAN ===== --}}
    <table class="detail-table">
        <tr class="section-header"><td colspan="3">I. DATA PEMOHON</td></tr>
        <tr><td>Nama Pemohon</td><td>:</td><td>{{ $member->name }}</td></tr>
        <tr><td>No. Anggota</td><td>:</td><td>{{ $member->member_no ?? '-' }}</td></tr>
        <tr><td>NIK / KTP</td><td>:</td><td>{{ $member->id_number ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td>:</td><td>{{ $member->address ?? '-' }}</td></tr>
        <tr><td>No. Telepon</td><td>:</td><td>{{ $member->phone ?? '-' }}</td></tr>

        <tr class="section-header"><td colspan="3">II. FASILITAS PEMBIAYAAN</td></tr>
        <tr><td>Jenis Pembiayaan</td><td>:</td><td>{{ $application->loanProduct?->name ?? 'Pembiayaan Konsumen' }}</td></tr>
        <tr><td>Plafond Pembiayaan</td><td>:</td><td><strong>Rp {{ number_format($principal, 0, ',', '.') }}</strong></td></tr>
        <tr><td>Jangka Waktu</td><td>:</td><td>{{ $tenor }} bulan</td></tr>
        <tr><td>Suku Bunga</td><td>:</td><td>{{ number_format($bungaPct, 2) }}% per bulan ({{ strtoupper($method) }})</td></tr>
        <tr><td>Angsuran per Bulan</td><td>:</td><td><strong>Rp {{ number_format($angsuran, 0, ',', '.') }}</strong></td></tr>
        <tr><td>Total Kewajiban</td><td>:</td><td>Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</td></tr>

        <tr class="section-header"><td colspan="3">III. BIAYA-BIAYA</td></tr>
        @if($provisi > 0)
        <tr><td>Biaya Provisi</td><td>:</td><td>Rp {{ number_format($provisi, 0, ',', '.') }} ({{ number_format($provisiPct, 1) }}%)</td></tr>
        @endif
        @if($adminFee > 0)
        <tr><td>Biaya Administrasi</td><td>:</td><td>Rp {{ number_format($adminFee, 0, ',', '.') }}</td></tr>
        @endif
        @if($insurance > 0)
        <tr><td>Biaya Asuransi</td><td>:</td><td>Rp {{ number_format($insurance, 0, ',', '.') }}</td></tr>
        @endif
        @if($provisi == 0 && $adminFee == 0 && $insurance == 0)
        <tr><td colspan="3" style="text-align:center;color:#666;font-style:italic;">Tidak ada biaya tambahan</td></tr>
        @endif

        <tr class="section-header"><td colspan="3">IV. JAMINAN / AGUNAN</td></tr>
        <tr><td>Jaminan</td><td>:</td><td>{{ $jaminanText }}</td></tr>
        @if(!empty($fd['njop_agunan']))
        <tr><td>NJOP</td><td>:</td><td>Rp {{ number_format($fd['njop_agunan'], 0, ',', '.') }}</td></tr>
        @endif
    </table>

    {{-- ===== SYARAT REALISASI ===== --}}
    <p style="margin-top:12pt;"><strong>Syarat-Syarat Realisasi Pembiayaan:</strong></p>
    <p>Untuk dapat merealisasikan pembiayaan ini, Saudara/i diwajibkan melengkapi persyaratan berikut:</p>
    <ol class="syarat-list">
        <li>Fotokopi KTP pemohon dan pasangan (apabila sudah menikah)</li>
        <li>Fotokopi Kartu Keluarga</li>
        <li>Fotokopi buku nikah / akta cerai (jika berlaku)</li>
        <li>Dokumen jaminan asli beserta fotokopi</li>
        <li>Slip gaji / bukti penghasilan 3 bulan terakhir</li>
        <li>Rekening koran / tabungan 3 bulan terakhir (jika ada)</li>
        <li>Menandatangani Perjanjian Kredit (PK) di hadapan petugas KSP</li>
        <li>Membayar biaya-biaya yang telah disebutkan di atas sebelum pencairan</li>
    </ol>

    {{-- ===== MASA BERLAKU ===== --}}
    <p style="margin-top:10pt;">
        Penawaran ini berlaku selama <strong>14 (empat belas) hari kalender</strong> sejak tanggal surat,
        yaitu sampai dengan <strong>{{ $berlakuHingga }}</strong>.
        Apabila dalam jangka waktu tersebut tidak ada konfirmasi dari Saudara/i, maka penawaran ini dianggap batal.
    </p>

    <p>
        Demikian penawaran ini kami sampaikan. Atas perhatian dan kepercayaan Saudara/i kepada KSP SMM,
        kami ucapkan terima kasih.
    </p>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-wrap">
        <table class="signature-table">
            <tr>
                <td>
                    {{-- Ruang kosong kiri --}}
                </td>
                <td>
                    <div>{{ $tglSurat }}</div>
                    <div style="margin-top:4pt;">Hormat kami,</div>
                    <div style="margin-top:2pt;font-weight:bold;">Koperasi Simpan Pinjam SMM</div>
                    <div class="signature-box">{{ $approverName }}</div>
                    <div style="font-size:9pt;color:#555;">Pimpinan / Manager</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== PENERIMAAN ===== --}}
    <div style="margin-top:20pt; border:1px dashed #999; padding:10pt;">
        <div style="font-size:9.5pt;font-weight:bold;margin-bottom:6pt;">TANDA TERIMA SURAT PENAWARAN</div>
        <div style="font-size:9.5pt;">
            Saya yang bertanda tangan di bawah ini menyatakan telah menerima dan memahami isi surat penawaran pembiayaan ini.
        </div>
        <table style="width:100%;margin-top:10pt;font-size:9.5pt;">
            <tr>
                <td style="width:50%">Nama &nbsp;&nbsp;: ___________________________</td>
                <td style="width:50%">Tanda Tangan &nbsp;: ___________________________</td>
            </tr>
            <tr>
                <td style="padding-top:6pt;">Tanggal : ___________________________</td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dokumen ini diterbitkan oleh Koperasi Simpan Pinjam SMM &bull; {{ $nomorSurat }} &bull; {{ $tglSurat }}
    </div>

</div>
</body>
</html>
