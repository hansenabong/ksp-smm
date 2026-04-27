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
        .surat-info td:first-child { width: 30pt; }
        .surat-info td:nth-child(2) { width: 14pt; }

        /* Recipient */
        .kepada { margin-bottom: 14pt; }

        /* Body */
        p { text-align: justify; margin-bottom: 8pt; }

        /* Detail table */
        .detail-table { width: 100%; border-collapse: collapse; margin: 10pt 0; }
        .detail-table td { padding: 3pt 5pt; vertical-align: top; border: 1px solid #aaa; font-size: 10.5pt; }
        .detail-table td:first-child { width: 38%; background: #f9f9f9; font-weight: bold; }
        .detail-table td:nth-child(2) { width: 4%; text-align: center; }
        .detail-table .section-header td { background: #000; color: #fff; font-weight: bold; text-align: center; }

        /* Pengikatan & Syarat */
        .label-bold { font-weight: bold; }
        .syarat-list { margin: 4pt 0 4pt 16pt; }
        .syarat-list li { margin-bottom: 3pt; text-align: justify; }

        /* Signature */
        .signature-wrap { margin-top: 24pt; }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { width: 33.33%; vertical-align: top; padding: 0 6pt; text-align: center; }
        .signature-box { margin-top: 56pt; border-top: 1px solid #000; padding-top: 4pt; }

        .footer { margin-top: 16pt; font-size: 8.5pt; color: #555; border-top: 1px solid #bbb; padding-top: 6pt; text-align: center; }
    </style>
</head>
<body>
<div class="page">

    @php
        $fd      = array_merge($application->form_data ?? [], $application->doc_overrides ?? []);
        $member  = $application->member;

        $principal  = $application->principal;
        $tenor      = $application->tenor_months;
        $bungaBp    = $application->interest_rate_monthly_bp ?? ($application->interest_monthly_percent * 100);
        $bungaPct   = $bungaBp / 100;
        $method     = $application->method ?? 'FLAT';
        $adminFee   = 0;
        $insurance  = ($application->insurance_monthly ?? 0) * $tenor;

        if ($loan && $loan->monthly_installment > 0) {
            $angsuran = $loan->monthly_installment;
        } elseif (strtoupper($method) === 'FLAT') {
            $totalBunga = $principal * ($bungaPct / 100) * $tenor;
            $angsuran   = round(($principal + $totalBunga) / $tenor);
        } else {
            $r = $bungaPct / 100;
            $angsuran = $r > 0
                ? round($principal * $r * pow(1+$r, $tenor) / (pow(1+$r, $tenor) - 1))
                : round($principal / $tenor);
        }

        $totalAngsuran = $angsuran * $tenor;

        $provisiPct = ($application->loanProduct?->provision_rate_percent ?? 0);
        $provisi    = round($principal * $provisiPct / 100);

        $tujuan = $fd['tujuan_pembiayaan'] ?? $application->notes ?? '-';

        // Bulan Indonesia
        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
        $tglNow = now();
        $tglSurat = $tglNow->day . ' ' . $bulanId[$tglNow->month] . ' ' . $tglNow->year;

        // OL type: 'kendaraan' or 'sertipikat'
        $isKendaraan = ($olType === 'kendaraan');

        // === Kendaraan fields ===
        $jenisKendaraan  = $fd['jenis_kendaraan'] ?? 'Kendaraan Roda Empat';
        $merkKendaraan   = trim(($fd['merk_kendaraan'] ?? '') . ' ' . ($fd['type_kendaraan'] ?? '')) ?: '-';
        $tahunKendaraan  = $fd['tahun_kendaraan']  ?? '-';
        $modelKendaraan  = $fd['model_kendaraan']  ?? '-';
        $bahanBakar      = $fd['bahan_bakar']       ?? '-';
        $noRangka        = $fd['no_rangka']         ?? '-';
        $noMesin         = $fd['no_mesin']          ?? '-';
        $noFaktur        = $fd['no_faktur']         ?? '-';
        $noPolisi        = $fd['no_polisi']         ?? '-';
        $warnaKendaraan  = $fd['warna_kendaraan']   ?? '-';
        $atasBpkb        = $fd['atas_nama_bpkb']    ?? $member?->name ?? '-';
        $noBpkb          = $fd['no_bpkb']           ?? '-';
        $obyekKendaraan  = 'Satu unit kendaraan roda empat dan BPKB Nomor ' . $noBpkb;

        // === Sertipikat fields ===
        $noHak      = $fd['no_hak']              ?? '....';
        $luasTanah  = $fd['luas_tanah']          ?? '....';
        $atasNama   = $fd['atas_nama_agunan']    ?? '....';
        $namaKampung = $fd['komplek_agunan']     ?? $fd['lokasi_agunan'] ?? '....';
        $jalanAgunan = $fd['jalan_agunan']       ?? '....';
        $noAgunan   = $fd['no_agunan']           ?? '....';
        $rtAgunan   = $fd['rt_agunan']           ?? '....';
        $rwAgunan   = $fd['rw_agunan']           ?? '....';
        $kelAgunan  = $fd['kelurahan_agunan']    ?? '....';
        $kecAgunan  = $fd['kecamatan_agunan']    ?? '....';
        $kabAgunan  = $fd['kota_agunan']         ?? '....';
        $penerbit   = $fd['penerbit_sertifikat'] ?? '....';
        $njop       = $fd['njop_agunan']         ?? 0;

        $obyekSertipikat = 'Sebidang tanah beserta bangunan Sertipikat Hak Milik Nomor '
            . $noHak . ' seluas ' . $luasTanah . ' m² atas nama ' . $atasNama
            . ' yang terletak di ' . $namaKampung . ', Jalan ' . $jalanAgunan
            . ' Nomor ' . $noAgunan
            . ', Rukun Tetangga ' . $rtAgunan . ', Rukun Warga ' . $rwAgunan
            . ', Kelurahan ' . $kelAgunan . ', Kecamatan ' . $kecAgunan
            . ', Kabupaten ' . $kabAgunan
            . ', yang dikeluarkan oleh pihak berwajib di ' . $penerbit;
    @endphp

    @php
        $__logo    = public_path('images/logo-ksp.jpeg');
        $__logoSrc = file_exists($__logo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($__logo)) : '';
    @endphp

    {{-- ===== KOP SURAT ===== --}}
    <div class="kop">
        <table class="kop-table kop-logo"><tr>
            <td style="width:70pt; padding-right:10pt; vertical-align:middle;">
                @if($__logoSrc)
                <img src="{{ $__logoSrc }}" alt="KSP SMM" style="width:60pt; height:60pt;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div class="kop-name">KSP SARANA MITRA MANDIRI</div>
                <div class="kop-sub">Jalan RS Fatmawati Komplek Duta Mas Blok B1 No.16, Kel. Cipete Utara</div>
                <div class="kop-sub">Kec. Kebayoran Baru, Kota Jakarta Selatan 12150</div>
                <div class="kop-sub">No. Telepon : 0812 1635 217</div>
            </td>
            <td style="width:70pt;"></td>
        </tr></table>
    </div>

    {{-- ===== INFO SURAT ===== --}}
    <div class="surat-info" style="margin-bottom:10pt;">
        <div>Jakarta, {{ $tglSurat }}</div>
        <div>No. {{ $nomorSurat }}</div>
    </div>

    {{-- ===== KEPADA ===== --}}
    <div class="kepada">
        <div>Kepada Yth,</div>
        <div><strong>Bapak / Ibu {{ $member->name }}</strong></div>
        <div>{{ $member->address ?? '' }}</div>
        <div style="margin-top:4pt;">Di ....................</div>
    </div>

    {{-- ===== PERIHAL ===== --}}
    <table style="border-collapse:collapse; margin-bottom:12pt;">
        <tr>
            <td style="width:100pt;">Perihal</td>
            <td style="width:12pt;">:</td>
            <td><strong>Penawaran Pembiayaan</strong></td>
        </tr>
    </table>

    {{-- ===== SALAM ===== --}}
    <p>Dengan hormat,</p>
    <p>
        Dalam rangka memenuhi permohonan Saudara, dengan ini kami KSP Sarana Mitra Mandiri
        mengajukan penawaran pembiayaan dengan kondisi dan persyaratan sebagai berikut:
    </p>

    {{-- ===== TABEL DETAIL ===== --}}
    <table class="detail-table">
        {{-- I. DATA PEMOHON --}}
        <tr class="section-header"><td colspan="3">I. DATA PEMOHON</td></tr>
        <tr><td>Nama Pemohon</td><td>:</td><td>{{ $member->name }}</td></tr>
        <tr><td>No. Anggota</td><td>:</td><td>{{ $member->member_no ?? '-' }}</td></tr>
        <tr><td>NIK / KTP</td><td>:</td><td>{{ $fd['no_ktp'] ?? $member->id_number ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td>:</td><td>{{ $member->address ?? '-' }}</td></tr>
        <tr><td>No. Telepon</td><td>:</td><td>{{ $member->phone ?? '-' }}</td></tr>

        {{-- II. FASILITAS PEMBIAYAAN --}}
        <tr class="section-header"><td colspan="3">II. FASILITAS PEMBIAYAAN</td></tr>
        <tr><td>Jenis Pembiayaan</td><td>:</td><td>{{ $application->loanProduct?->name ?? 'Pembiayaan Konsumen' }}</td></tr>
        <tr><td>Plafond Pembiayaan</td><td>:</td><td><strong>Rp {{ number_format($principal, 0, ',', '.') }}</strong></td></tr>
        <tr><td>Tujuan Penggunaan</td><td>:</td><td>{{ $tujuan }}</td></tr>
        <tr>
            <td>Obyek Pembiayaan</td>
            <td>:</td>
            <td>
                @if($isKendaraan)
                    {{ $obyekKendaraan }}
                    <table style="border-collapse:collapse; margin-top:4pt; font-size:10pt; width:100%;">
                        <tr><td style="width:40%; background:none; border:none; padding:1pt 0; font-weight:normal;">Jenis Kendaraan</td><td style="border:none; padding:1pt 2pt; width:4%; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $jenisKendaraan }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Merk/Type</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $merkKendaraan }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Tahun</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $tahunKendaraan }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Model</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $modelKendaraan }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Bahan Bakar</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $bahanBakar }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">No. Rangka</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $noRangka }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">No. Mesin</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $noMesin }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">No. Faktur</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $noFaktur }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">No. Polisi</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $noPolisi }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Warna Kendaraan</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $warnaKendaraan }}</td></tr>
                        <tr><td style="background:none; border:none; padding:1pt 0; font-weight:normal;">Terdaftar atas nama</td><td style="border:none; padding:1pt 2pt; background:none; font-weight:normal;">:</td><td style="border:none; padding:1pt 0; background:none; font-weight:normal;">{{ $atasBpkb }}</td></tr>
                    </table>
                @else
                    {{ $obyekSertipikat }}
                @endif
            </td>
        </tr>
        <tr><td>Jangka Waktu</td><td>:</td><td>{{ $tenor }} ({{ $tenor }}) bulan terhitung tanggal pencairan fasilitas ini</td></tr>
        <tr><td>Angsuran / Bulan</td><td>:</td><td><strong>Rp {{ number_format($angsuran, 0, ',', '.') }},-</strong></td></tr>
        <tr><td>Total Kewajiban</td><td>:</td><td>Rp {{ number_format($totalAngsuran, 0, ',', '.') }}</td></tr>

        {{-- III. BIAYA-BIAYA --}}
        <tr class="section-header"><td colspan="3">III. BIAYA-BIAYA</td></tr>
        @if($provisi > 0)
        <tr><td>Biaya Provisi</td><td>:</td><td>Setara {{ number_format($provisiPct, 0) }}% flat dibayar tunai pada saat pencairan (Rp {{ number_format($provisi, 0, ',', '.') }})</td></tr>
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

        {{-- IV. JAMINAN / AGUNAN --}}
        <tr class="section-header"><td colspan="3">IV. JAMINAN / AGUNAN</td></tr>
        @if($isKendaraan)
        <tr><td>Jaminan</td><td>:</td><td>{{ $jenisKendaraan }} {{ $merkKendaraan }}, No. Polisi {{ $noPolisi }}, Tahun {{ $tahunKendaraan }}, atas nama {{ $atasBpkb }}</td></tr>
        @else
        <tr><td>Jaminan</td><td>:</td><td>Sertipikat Hak Milik No. {{ $noHak }}, luas {{ $luasTanah }} m², atas nama {{ $atasNama }}, {{ $kelAgunan }}, {{ $kecAgunan }}</td></tr>
        @if($njop > 0)
        <tr><td>NJOP</td><td>:</td><td>Rp {{ number_format($njop, 0, ',', '.') }}</td></tr>
        @endif
        @endif
    </table>

    {{-- ===== PENGIKATAN ===== --}}
    <table style="border-collapse:collapse; margin-bottom:6pt; width:100%;">
        <tr>
            <td style="width:38%; font-weight:bold; vertical-align:top; padding:2pt 0;">Pengikatan</td>
            <td style="width:4%; vertical-align:top; padding:2pt 2pt;">:</td>
            <td style="vertical-align:top; padding:2pt 0;">
                @if($isKendaraan)
                    a. Secara notaril untuk pengikatan Fidusia<br>
                    b. Secara di Bawah Tangan untuk Perjanjian Kredit
                @else
                    a. Secara notaril untuk Surat Kuasa Memasang Hak Tanggungan (SKMHT) dan Akta Pemberian Hak Tanggungan (APHT)<br>
                    b. Secara di Bawah Tangan untuk Perjanjian Pembiayaan
                @endif
            </td>
        </tr>
    </table>

    {{-- ===== SYARAT-SYARAT ===== --}}
    <table style="border-collapse:collapse; margin-bottom:10pt; width:100%;">
        <tr>
            <td style="width:38%; font-weight:bold; vertical-align:top; padding:2pt 0;">Syarat-syarat</td>
            <td style="width:4%; vertical-align:top; padding:2pt 2pt;">:</td>
            <td style="vertical-align:top; padding:2pt 0;">
                a. &nbsp;Obyek pembiayaan harus atas nama debitur dan tidak terikat sebagai jaminan atau dalam sengketa dengan pihak ketiga.<br>
                b. &nbsp;Biaya Notaris, Asuransi, Appraisal dan biaya-biaya lain yang timbul dan yang akan timbul sehubungan dengan fasilitas pembiayaan ini menjadi beban debitur.<br>
                c. &nbsp;Pelunasan dipercepat dapat dilakukan setelah 3 (tiga) kali masa angsuran dan dikenakan biaya pelunasan dipercepat sebesar setara 5% dari hutang pokok tersisa.<br>
                d. &nbsp;Setiap terjadi keterlambatan pembayaran angsuran dikenakan denda sebesar setara 0,3% per hari.<br>
                e. &nbsp;Lain-lain sesuai dengan ketentuan KSP Sarana Mitra Mandiri.
            </td>
        </tr>
    </table>

    {{-- ===== DISCLAIMER ===== --}}
    <p>
        Surat penawaran pembiayaan ini bukan merupakan komitmen KSP Sarana Mitra Mandiri dalam
        memberikan fasilitas pembiayaan sampai dengan ditandatanganinya seluruh perjanjian-perjanjian
        baik secara notaril maupun dibawah tangan dan setelah seluruh syarat dipenuhi.
    </p>

    {{-- ===== PENUTUP ===== --}}
    <p>
        Demikian penawaran pembiayaan kami semoga memenuhi kebutuhan Saudara. Apabila Saudara
        menyetujui seluruh kondisi dalam persyaratan diatas, harap setiap halaman surat diparaf dan
        ditanda tangani pada halaman terakhir diatas meterai sesuai dengan ketentuan, dan segera
        dikembalikan kepada kami selambat-lambatnya 7 (tujuh) hari sejak tanggal surat penawaran ini.
    </p>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-wrap">
        <table class="signature-table">
            <tr>
                <td style="text-align:left; vertical-align:top; width:33.33%;">
                    <div>Hormat kami,</div>
                    <div style="font-weight:bold;">KSP. Sarana Mitra Mandiri</div>
                    <div style="margin-top:56pt; border-top:1px solid #000; padding-top:4pt;">
                        ( Go Tji Seng / Ricky )<br>
                        <span style="font-size:9pt;">Ketua KSP SMM</span>
                    </div>
                </td>
                <td style="text-align:center; vertical-align:top; width:33.33%;">
                    <div>Menyetujui</div>
                    <div style="font-size:9pt; border:1px solid #000; padding:2pt 8pt; margin-top:2pt;">Materai 10.000</div>
                    <div style="margin-top:16pt; border-top:1px solid #000; padding-top:4pt;">
                        ( ..................................... )<br>
                        <span style="font-size:9pt;">Debitur</span>
                    </div>
                </td>
                <td style="text-align:center; vertical-align:top; width:33.33%;">
                    <div>&nbsp;</div>
                    <div style="margin-top:56pt; border-top:1px solid #000; padding-top:4pt;">
                        ( ..................................... )<br>
                        <span style="font-size:9pt;">Suami/Istri</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dokumen ini diterbitkan oleh Koperasi Simpan Pinjam SMM &bull; {{ $nomorSurat }} &bull; {{ $tglSurat }}
    </div>

</div>
</body>
</html>
