<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perjanjian Pembiayaan {{ $nomorPk }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.5;
        }
        .page { padding: 2cm 2.5cm 2cm 2.5cm; }
        .header { text-align: center; margin-bottom: 18pt; }
        .header h1 { font-size: 14pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; text-transform: uppercase; }
        .header .nomor { font-size: 11pt; margin-top: 4pt; }
        .divider { border-top: 2px solid #000; margin: 8pt 0; }
        .opening { text-align: justify; margin-bottom: 10pt; }
        .party { margin-bottom: 10pt; }
        .party-num { float: left; width: 28pt; font-weight: bold; }
        .party-content { margin-left: 28pt; text-align: justify; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .selanjutnya { text-align: justify; margin: 10pt 0; }
        .pasal-block { page-break-inside: avoid; margin-top: 14pt; }
        .pasal-header { text-align: center; font-weight: bold; margin-bottom: 2pt; }
        .pasal-title { text-align: center; font-weight: bold; margin-bottom: 8pt; }
        .pasal-item { margin-bottom: 6pt; text-align: justify; }
        .pasal-item-num { float: left; width: 20pt; }
        .pasal-item-text { margin-left: 20pt; }
        .sub-list { margin-top: 4pt; }
        .sub-list-item { margin-bottom: 3pt; }
        .sub-list-item-num { float: left; width: 24pt; }
        .sub-list-item-text { margin-left: 24pt; text-align: justify; }
        .detail-table { width: 100%; margin: 6pt 0; border-collapse: collapse; }
        .detail-table td { padding: 2pt 4pt; vertical-align: top; }
        .detail-table td:first-child { width: 44%; }
        .detail-table td:nth-child(2) { width: 4%; text-align: center; }
        .signature-section { margin-top: 24pt; }
        .signature-grid { width: 100%; }
        .signature-grid td { width: 33%; vertical-align: top; text-align: center; padding: 0 6pt; }
        .signature-box { margin-top: 60pt; border-top: 1px solid #000; padding-top: 4pt; }
        .footer-note { margin-top: 16pt; font-size: 9pt; color: #444; border-top: 1px solid #aaa; padding-top: 6pt; text-align: center; }
        .kop { border-bottom: 3px double #000; padding-bottom: 8pt; margin-bottom: 14pt; }
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-logo td { vertical-align: middle; }
        .kop-name { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-sub  { font-size: 9pt; color: #444; margin-top: 2pt; }
        .kop-addr { font-size: 8.5pt; color: #444; margin-top: 1pt; }
    </style>
</head>
<body>
<div class="page">

    {{-- ===== KOP SURAT ===== --}}
    <div class="kop">
        <table class="kop-table kop-logo"><tr>
            <td style="width:60pt; padding-right:10pt;">
                <img src="{{ public_path('images/logo-ksp.jpeg') }}" alt="KSP SMM" style="width:54pt; height:54pt; object-fit:contain;">
            </td>
            <td>
                <div class="kop-name">Koperasi Simpan Pinjam Sarana Mitra Mandiri</div>
                <div class="kop-sub">Koperasi Simpan Pinjam</div>
                <div class="kop-addr">Komplek Duta Mas Fatmawati Blok B1 No 16, Jakarta Selatan 12150</div>
            </td>
        </tr></table>
    </div>

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <h1>PERJANJIAN KREDIT</h1>
        <div class="nomor">Nomor: {{ $nomorPk }}</div>
    </div>
    <div class="divider"></div>

    @php
        $fd       = $application->form_data ?? [];
        $member   = $application->member;
        $disbDate = $loan?->disbursed_date ?? $application->trx_date;

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];

        $tglStr   = $disbDate->day . ' ' . $bulanId[$disbDate->month] . ' ' . $disbDate->year;
        $tglLunas = $disbDate->copy()->addMonths($application->tenor_months ?? $loan?->tenor_months ?? 0);

        $principal    = $loan?->principal                ?? $application->principal;
        $tenor        = $loan?->tenor_months             ?? $application->tenor_months;
        $bunga        = $loan?->interest_monthly_percent ?? $application->interest_monthly_percent;
        $angsuran     = $loan?->monthly_installment      ?? 0;
        $totalPayable = $loan?->total_payable            ?? 0;
        $dueDay       = $disbDate->day;

        // Penalty & prepayment from product
        $penaltyBp   = $application->loanProduct?->penalty_daily_rate_bp   ?? 30;
        $prepayBp    = $application->loanProduct?->early_repayment_rate_bp ?? 500;
        $penaltyPct  = number_format($penaltyBp  / 100, 1);   // e.g. "0.3"
        $prepayPct   = number_format($prepayBp   / 100, 0);   // e.g. "5"
        $penaltyMonth= number_format($penaltyBp  / 100 * 30, 0); // approx /month

        // Notaris
        $hasNotaris  = !empty($application->notaris_deed_number);
        $notarisDate = $application->notaris_deed_date
            ? $application->notaris_deed_date->day . ' ' . $bulanId[$application->notaris_deed_date->month] . ' ' . $application->notaris_deed_date->year
            : null;

        // Disbursement fees
        $disbFees  = $loan?->disbursement_fees ?? [];
        $feeLabels = [
            'biaya_administrasi' => 'Biaya Administrasi',
            'biaya_provisi'      => 'Biaya Provisi',
            'biaya_notaris'      => 'Biaya Notaris',
            'asuransi'           => 'Asuransi',
            'pengecekan'         => 'Pengecekan',
            'ploting'            => 'Ploting',
            'roya'               => 'Roya',
            'pnpb'               => 'PNPB',
            'hold_imb'           => 'Hold IMB',
            'hold_pbb'           => 'Hold PBB',
            'hold_angsuran'      => 'Hold Angsuran',
            'bphtb'              => 'BPHTB',
            'pph'                => 'PPh',
        ];
        $totalFees    = (int) array_sum(array_filter($disbFees, fn($v) => (int)$v > 0));
        $netDisbursed = (int)$principal - $totalFees;
        $hasFees      = !empty($disbFees) && $totalFees > 0;

        $adminFee = $application->admin_fee ?? $application->admin_monthly ?? 0;
        $tujuan   = $fd['tujuan_pembiayaan'] ?? $application->notes ?? null;

        $hasProperty = !empty($fd['no_hak'] ?? $fd['lokasi_agunan'] ?? $fd['kota_agunan'] ?? null);
        $hasVehicle  = !empty($fd['jenis_kendaraan'] ?? null);

        // Build structured property address
        $propAddr = [];
        if (!empty($fd['komplek_agunan']))  $propAddr[] = 'Komplek ' . $fd['komplek_agunan'];
        if (!empty($fd['jalan_agunan']))    $propAddr[] = 'Jalan '   . $fd['jalan_agunan'];
        if (!empty($fd['blok_agunan']))     $propAddr[] = 'Blok '    . $fd['blok_agunan'];
        if (!empty($fd['no_agunan']))       $propAddr[] = 'No. '     . $fd['no_agunan'];
        if (!empty($fd['rt_agunan']))       $propAddr[] = 'RT '      . ($fd['rt_agunan'] ?? '') . '/RW ' . ($fd['rw_agunan'] ?? '');
        if (!empty($fd['kelurahan_agunan'])) $propAddr[] = 'Kel. '   . $fd['kelurahan_agunan'];
        if (!empty($fd['kecamatan_agunan'])) $propAddr[] = 'Kec. '   . $fd['kecamatan_agunan'];
        if (!empty($fd['kota_agunan']))     $propAddr[] = $fd['kota_agunan'];
        $propAddrStr = !empty($propAddr) ? implode(', ', $propAddr) : ($fd['lokasi_agunan'] ?? '___________________________');

        // Dynamic pasal numbering
        $pasalNo = 1;
    @endphp

    {{-- ===== PEMBUKA ===== --}}
    <p class="opening">
        Pada hari ini, tanggal <strong>{{ $tglStr }}</strong>,
        diadakan Perjanjian Pembiayaan Konsumen yang kemudian disebut <em>"Perjanjian"</em> antara:
    </p>

    <div class="party clearfix">
        <div class="party-num">I.</div>
        <div class="party-content">
            <strong>Koperasi Simpan Pinjam Sarana Mitra Mandiri</strong>, suatu badan usaha berbentuk
            Koperasi Simpan Pinjam berkedudukan di Jakarta, dalam hal ini diwakili oleh
            <strong>Go Tji Seng</strong>, dalam kedudukannya sebagai <strong>Ketua Koperasi</strong>,
            sah mewakili Koperasi, selanjutnya disebut <strong><em>"Pihak Pertama (Koperasi)"</em></strong>.
        </div>
    </div>

    <div class="party clearfix">
        <div class="party-num">II.</div>
        <div class="party-content">
            <strong>{{ $fd['nama_lengkap'] ?? $member?->name ?? '___________________________' }}</strong>,
            pekerjaan <strong>{{ $fd['pekerjaan'] ?? '_______________' }}</strong>,
            bertempat tinggal di
            {{ $fd['alamat'] ?? $member?->address ?? '___________________________' }}
            @if(!empty($fd['rt'] ?? null) && !empty($fd['rw'] ?? null)) RT {{ $fd['rt'] }}/RW {{ $fd['rw'] }},@endif
            @if(!empty($fd['kota'] ?? null)) {{ $fd['kota'] }}{{ !empty($fd['kode_pos'] ?? null) ? ' '.$fd['kode_pos'] : '' }},@endif
            pemegang KTP Nomor <strong>{{ $fd['no_ktp'] ?? '___________________________' }}</strong>,
            @if(!empty($fd['nama_pasangan'] ?? null))
                dengan persetujuan dari {{ ($fd['kelamin'] ?? '') === 'L' ? 'istri' : 'suami' }}
                bernama <strong>{{ $fd['nama_pasangan'] }}</strong> yang turut menandatangani Perjanjian ini,
            @endif
            selanjutnya disebut <strong><em>"Pihak Kedua"</em></strong>.
        </div>
    </div>

    @if($hasNotaris)
    <p class="opening" style="margin-top: 8pt;">
        Perjanjian ini dibuat berdasarkan Akta Notaris
        Nomor <strong>{{ $application->notaris_deed_number }}</strong>
        tanggal <strong>{{ $notarisDate }}</strong>
        yang dibuat di hadapan Notaris <strong>{{ $application->notaris_name }}</strong>@if($application->notaris_city), berkedudukan di {{ $application->notaris_city }}@endif.
    </p>
    @endif

    <p class="selanjutnya">
        Selanjutnya <em>"Pihak Pertama"</em> dan <em>"Pihak Kedua"</em> disebut <strong><em>"Para Pihak"</em></strong>.
    </p>
    <p class="opening">
        Sebelum menandatangani <em>"Perjanjian"</em> ini, <em>"Para Pihak"</em> menerangkan bahwa
        <em>"Pihak Kedua"</em> berkeinginan mendapatkan fasilitas pembiayaan dari <em>"Pihak Pertama"</em>;
        sehubungan dengan itu, <em>"Para Pihak"</em> telah sepakat membuat <em>"Perjanjian"</em>
        dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:
    </p>

    {{-- ===== PASAL 1 — PLAFOND DAN JANGKA WAKTU ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Plafond dan Jangka Waktu</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            <em>"Pihak Pertama"</em> memberikan fasilitas pembiayaan kepada <em>"Pihak Kedua"</em>
            dengan ketentuan sebagai berikut:
            <table class="detail-table">
                <tr><td>Produk Pembiayaan</td><td>:</td><td><strong>{{ $application->loanProduct?->name ?? '-' }}</strong></td></tr>
                <tr><td>Pokok Pinjaman (Plafond)</td><td>:</td><td><strong>Rp {{ number_format($principal, 0, ',', '.') }}</strong> ({{ terbilang($principal) }} rupiah)</td></tr>
                <tr><td>Jangka Waktu</td><td>:</td><td><strong>{{ $tenor }} ({{ terbilang($tenor) }}) bulan</strong></td></tr>
                <tr><td>Suku Bunga Flat per Bulan</td><td>:</td><td><strong>{{ $bunga }}%</strong> ({{ number_format($bunga * 12, 0) }}% per tahun flat)</td></tr>
                <tr><td>Angsuran per Bulan</td><td>:</td><td><strong>Rp {{ number_format($angsuran, 0, ',', '.') }},-</strong> @if($angsuran > 0)({{ terbilang($angsuran) }} rupiah)@endif</td></tr>
                @if($adminFee)<tr><td>Biaya Provisi / Admin</td><td>:</td><td>Rp {{ number_format($adminFee, 0, ',', '.') }},-</td></tr>@endif
                @if($application->insurance_monthly)<tr><td>Biaya Asuransi per Bulan</td><td>:</td><td>Rp {{ number_format($application->insurance_monthly, 0, ',', '.') }},-</td></tr>@endif
                @if($totalPayable)<tr><td>Total Kewajiban</td><td>:</td><td><strong>Rp {{ number_format($totalPayable, 0, ',', '.') }},-</strong></td></tr>@endif
                @if($hasFees)
                <tr><td>Total Biaya Pencairan</td><td>:</td><td>Rp {{ number_format($totalFees, 0, ',', '.') }},-</td></tr>
                <tr><td>Dana Diterima Bersih</td><td>:</td><td><strong>Rp {{ number_format($netDisbursed, 0, ',', '.') }},-</strong></td></tr>
                @endif
                <tr><td>Tanggal Pencairan</td><td>:</td><td><strong>{{ $tglStr }}</strong></td></tr>
                <tr><td>Jatuh Tempo Akhir</td><td>:</td><td><strong>{{ $tglLunas->day }} {{ $bulanId[$tglLunas->month] }} {{ $tglLunas->year }}</strong></td></tr>
            </table>
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            <em>"Perjanjian"</em> ini dibuat untuk jangka waktu <strong>{{ $tenor }} ({{ terbilang($tenor) }}) bulan</strong>,
            terhitung sejak tanggal pencairan dan wajib lunas pada tanggal
            <strong>{{ $tglLunas->day }} {{ $bulanId[$tglLunas->month] }} {{ $tglLunas->year }}</strong>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL 2 — TUJUAN PENGGUNAAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Tujuan Penggunaan</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Fasilitas pembiayaan ini hanya dapat digunakan oleh <em>"Pihak Kedua"</em> semata-mata
            untuk keperluan <strong>{{ $tujuan ?? '___________________________' }}</strong>.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            <em>"Pihak Kedua"</em> tidak diperkenankan mengalihkan penggunaan fasilitas ini untuk
            tujuan lain tanpa persetujuan tertulis <em>"Pihak Pertama"</em>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL 3 — OBYEK PEMBIAYAAN (jika ada jaminan) ===== --}}
    @if($hasProperty || $hasVehicle)
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Obyek Pembiayaan</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Atas pembiayaan tersebut <em>"Pihak Kedua"</em> menyerahkan dan <em>"Pihak Pertama"</em>
            menerima obyek pembiayaan sebagai berikut:
            <table class="detail-table">
                @if($hasProperty)
                <tr><td>Jenis Jaminan</td><td>:</td><td>{{ $fd['status_agunan'] ?? 'Tanah dan/atau Bangunan' }}</td></tr>
                <tr><td>Nomor Sertifikat</td><td>:</td><td><strong>{{ $fd['no_hak'] ?? '_______________' }}</strong></td></tr>
                <tr><td>Luas</td><td>:</td><td>{{ $fd['luas_tanah'] ?? '___' }} m²@if(!empty($fd['luas_bangunan'] ?? null)) (bangunan {{ $fd['luas_bangunan'] }} m²)@endif</td></tr>
                <tr><td>Atas Nama</td><td>:</td><td><strong>{{ $fd['atas_nama_agunan'] ?? '___________________________' }}</strong></td></tr>
                <tr><td>Lokasi / Alamat</td><td>:</td><td>{{ $propAddrStr }}</td></tr>
                @if(!empty($fd['penerbit_sertifikat'] ?? null))<tr><td>Diterbitkan Oleh</td><td>:</td><td>{{ $fd['penerbit_sertifikat'] }}</td></tr>@endif
                @if(!empty($fd['tgl_sertifikat'] ?? null))
                    @php $tglSert = \Carbon\Carbon::parse($fd['tgl_sertifikat']); @endphp
                    <tr><td>Tanggal Penerbitan</td><td>:</td><td>{{ $tglSert->day }} {{ $bulanId[$tglSert->month] }} {{ $tglSert->year }}</td></tr>
                @endif
                @if(!empty($fd['nilai_agunan'] ?? null))<tr><td>Nilai NJOP</td><td>:</td><td>Rp {{ number_format($fd['nilai_agunan'], 0, ',', '.') }}</td></tr>@endif
                @endif
                @if($hasVehicle)
                <tr><td>Jenis Kendaraan</td><td>:</td><td>{{ $fd['jenis_kendaraan'] }} Tahun {{ $fd['tahun_kendaraan'] ?? '' }}, Warna {{ $fd['warna_kendaraan'] ?? '' }}</td></tr>
                <tr><td>No. BPKB</td><td>:</td><td>{{ $fd['no_bpkb'] ?? '-' }}</td></tr>
                <tr><td>Atas Nama BPKB</td><td>:</td><td>{{ $fd['atas_nama_bpkb'] ?? '-' }}</td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Berikut segala sesuatu yang berdiri dan tertanam di atas tanah/obyek tersebut.
        </div>
    </div>
    </div>{{-- /pasal-block --}}
    @endif

    {{-- ===== PASAL — ANGSURAN DAN BUNGA ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Angsuran dan Bunga</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            <em>"Pihak Kedua"</em> wajib membayar angsuran selama <strong>{{ $tenor }} ({{ terbilang($tenor) }}) bulan</strong>,
            sebesar <strong>Rp {{ number_format($angsuran, 0, ',', '.') }},- ({{ terbilang($angsuran) }} rupiah)</strong>
            per bulan, yang dibayarkan setiap tanggal <strong>{{ $dueDay }}</strong> setiap bulan berjalan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Angsuran di atas dihitung dengan tingkat suku bunga sebesar
            <strong>{{ $bunga }}% per bulan flat</strong>
            (setara <strong>{{ number_format($bunga * 12, 0) }}% per tahun flat</strong>), dan dapat direview.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — PROVISI (jika ada) ===== --}}
    @if($adminFee)
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Provisi dan Biaya-Biaya</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Selain kewajiban angsuran, <em>"Pihak Kedua"</em> wajib membayar di muka biaya provisi/administrasi
            sebesar <strong>Rp {{ number_format($adminFee, 0, ',', '.') }},-</strong>
            yang diperhitungkan pada saat pencairan fasilitas.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Provisi yang telah dibayarkan tidak dapat ditarik kembali dengan alasan apa pun.
        </div>
    </div>
    </div>{{-- /pasal-block --}}
    @endif

    {{-- ===== PASAL — RINCIAN BIAYA PENCAIRAN (jika ada) ===== --}}
    @if($hasFees)
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Rincian Biaya Pencairan</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Pada saat pencairan, dipotong biaya-biaya sebagai berikut:
            <table class="detail-table" style="margin-top: 4pt;">
                @foreach($disbFees as $key => $amount)
                    @if((int)$amount > 0)
                    <tr>
                        <td>{{ $feeLabels[$key] ?? $key }}</td>
                        <td>:</td>
                        <td>Rp {{ number_format((int)$amount, 0, ',', '.') }},-</td>
                    </tr>
                    @endif
                @endforeach
                <tr style="border-top: 1px solid #000; font-weight: bold;">
                    <td>Total Biaya</td><td>:</td><td>Rp {{ number_format($totalFees, 0, ',', '.') }},-</td>
                </tr>
                <tr style="font-weight: bold;">
                    <td>Dana Diterima Bersih</td><td>:</td>
                    <td>Rp {{ number_format($netDisbursed, 0, ',', '.') }},- ({{ terbilang($netDisbursed) }} rupiah)</td>
                </tr>
            </table>
        </div>
    </div>
    </div>{{-- /pasal-block --}}
    @endif

    {{-- ===== PASAL — DENDA KETERLAMBATAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Denda Keterlambatan</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Atas setiap keterlambatan pembayaran angsuran, <em>"Pihak Kedua"</em> dikenai denda
            sebesar <strong>{{ $penaltyPct }}% per hari</strong> atau
            <strong>{{ $penaltyMonth }}% per bulan</strong>, dihitung dari besarnya kewajiban tertunggak.
            Denda wajib dibayar bersama-sama dengan angsuran yang tertunggak.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Apabila <em>"Pihak Kedua"</em> hanya membayar angsuran tanpa denda, maka <em>"Pihak Pertama"</em>
            berhak memperhitungkan pembayaran tersebut terlebih dahulu untuk pelunasan denda sebelum pokok.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — PELUNASAN DIPERCEPAT ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Pelunasan Dipercepat</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Pelunasan sebelum jatuh tempo (<em>prepayment</em>) dapat dilakukan dengan pemberitahuan
            tertulis minimal 1 (satu) bulan sebelumnya, setelah <em>"Perjanjian"</em> ini berjalan
            paling sedikit 3 (tiga) bulan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Dalam hal pelunasan dipercepat, <em>"Pihak Kedua"</em> dikenai biaya penalti sebesar
            <strong>{{ $prepayPct }}% ({{ terbilang((int)$prepayPct) }} persen)</strong> dari sisa pokok
            yang masih terhutang, ditambah biaya lain yang masih belum dibayar.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">3.</div>
        <div class="pasal-item-text">
            Biaya provisi dan administrasi yang sudah dibayarkan tidak dapat ditarik kembali.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — ASURANSI ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Asuransi</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Selama <em>"Pihak Kedua"</em> masih berhutang kepada <em>"Pihak Pertama"</em>,
            <em>"Pihak Kedua"</em> diwajibkan mengikuti Asuransi Kematian, Kebakaran, dan lain sebagainya
            sesuai ketentuan <em>"Pihak Pertama"</em>.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Apabila terjadi klaim, uang ganti rugi dari perusahaan asuransi akan dipergunakan untuk
            melunasi hutang <em>"Pihak Kedua"</em> kepada <em>"Pihak Pertama"</em> beserta bunga dan
            biaya-biaya terkait. Kelebihan klaim, jika ada, akan diserahkan kepada <em>"Pihak Kedua"</em>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — TEMPAT PEMBAYARAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Tempat Pembayaran</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Pembayaran dapat dilakukan di Kantor <em>"Pihak Pertama"</em> yaitu
            <strong>Koperasi Simpan Pinjam Sarana Mitra Mandiri</strong>,
            Komplek Duta Mas Fatmawati Blok B1 No 16, Jakarta Selatan 12150,
            atau di alamat lain yang ditunjuk oleh <em>"Pihak Pertama"</em>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — PENYERAHAN DOKUMEN (jika ada jaminan) ===== --}}
    @if($hasProperty || $hasVehicle)
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Penyerahan dan Pengembalian Dokumen / Sertipikat</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            <em>"Pihak Kedua"</em> wajib menyerahkan semua bukti kepemilikan obyek pembiayaan kepada
            <em>"Pihak Pertama"</em> untuk disimpan selama hutang belum dibayar lunas.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Setelah <em>"Pihak Kedua"</em> melunasi seluruh kewajibannya, <em>"Pihak Pertama"</em>
            wajib mengembalikan semua bukti kepemilikan paling lambat <strong>14 (empat belas) hari kerja</strong>
            setelah pelunasan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">3.</div>
        <div class="pasal-item-text">
            Selama <em>"Perjanjian"</em> berlaku, <em>"Pihak Kedua"</em> dilarang untuk menjual,
            memindahkan, atau menjaminkan obyek pembiayaan kepada pihak lain tanpa izin tertulis
            dari <em>"Pihak Pertama"</em>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}
    @endif

    {{-- ===== PASAL — WANPRESTASI ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Wanprestasi</div>
    @php $pasalNo++; @endphp
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Peristiwa-peristiwa berikut ini merupakan wanprestasi <em>"Pihak Kedua"</em>:
            <div class="sub-list">
                <div class="sub-list-item clearfix">
                    <div class="sub-list-item-num">a.</div>
                    <div class="sub-list-item-text"><em>"Pihak Kedua"</em> tidak membayar angsuran, denda, dan/atau biaya lain selama 2 (dua) bulan berturut-turut.</div>
                </div>
                <div class="sub-list-item clearfix">
                    <div class="sub-list-item-num">b.</div>
                    <div class="sub-list-item-text">Obyek pembiayaan dijual, disewakan, dipindahtangankan, atau dijaminkan tanpa persetujuan tertulis <em>"Pihak Pertama"</em>.</div>
                </div>
                <div class="sub-list-item clearfix">
                    <div class="sub-list-item-num">c.</div>
                    <div class="sub-list-item-text"><em>"Pihak Kedua"</em> ditaruh di bawah pengampuan (curatele) atau meninggal dunia.</div>
                </div>
                <div class="sub-list-item clearfix">
                    <div class="sub-list-item-num">d.</div>
                    <div class="sub-list-item-text">Pernyataan atau dokumen yang diberikan <em>"Pihak Kedua"</em> terbukti tidak benar/palsu.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Dengan terjadinya salah satu hal di atas, <em>"Pihak Kedua"</em> dan/atau ahli warisnya wajib
            membayar seluruh sisa pokok, bunga, dan denda sekaligus pada saat yang ditentukan oleh
            <em>"Pihak Pertama"</em>.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== PASAL — HUKUM DAN YURISDIKSI ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal {{ $pasalNo }}</div>
    <div class="pasal-title">Hukum Yang Berlaku dan Yurisdiksi</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            <em>"Perjanjian"</em> ini tunduk pada Hukum Republik Indonesia.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Apabila terjadi perselisihan, <em>"Para Pihak"</em> menyelesaikannya secara musyawarah
            mufakat; jika tidak tercapai kesepakatan, <em>"Para Pihak"</em> memilih domisili hukum di
            Kantor Kepaniteraan Pengadilan Negeri Jakarta Selatan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">3.</div>
        <div class="pasal-item-text">
            <em>"Perjanjian"</em> ini dibuat dalam rangkap 2 (dua), masing-masing bermaterai cukup dan
            mempunyai kekuatan hukum yang sama.
        </div>
    </div>
    </div>{{-- /pasal-block --}}

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-section">
        <p style="text-align:center; margin-bottom: 8pt;">
            Demikian <em>"Perjanjian"</em> ini dibuat dan ditandatangani di Jakarta
            pada hari dan tanggal tersebut di atas.
        </p>
        <table class="signature-grid">
            <tr>
                <td>
                    <strong>Pihak Pertama (Koperasi)</strong><br>
                    KSP Sarana Mitra Mandiri
                    <div class="signature-box">
                        ( Go Tji Seng / Ricky )<br>
                        <small>Ketua KSP Sarana Mitra Mandiri</small>
                    </div>
                </td>
                <td>
                    <strong>Pihak Kedua</strong><br>
                    Pemohon
                    <div class="signature-box">
                        <small style="display:block; margin-bottom: 50pt;">(Meterai Rp 10.000)</small>
                        ( {{ $fd['nama_lengkap'] ?? $member?->name ?? '___________________________' }} )<br>
                        <small>No. KTP: {{ $fd['no_ktp'] ?? '-' }}</small>
                    </div>
                </td>
                @if(!empty($fd['nama_pasangan'] ?? null))
                <td>
                    <strong>Persetujuan {{ ($fd['kelamin'] ?? '') === 'L' ? 'Istri' : 'Suami' }}</strong><br>
                    &nbsp;
                    <div class="signature-box">
                        ( {{ $fd['nama_pasangan'] }} )<br>
                        <small>No. KTP: {{ $fd['no_ktp_pasangan'] ?? '-' }}</small>
                    </div>
                </td>
                @endif
            </tr>
        </table>

        <table class="signature-grid" style="margin-top: 32pt;">
            <tr>
                <td><strong>Saksi 1</strong><div class="signature-box">( ___________________________ )</div></td>
                <td><strong>Saksi 2</strong><div class="signature-box">( ___________________________ )</div></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Dicetak oleh sistem &bull; {{ now()->format('d/m/Y H:i') }} &bull; {{ $nomorPk }}
    </div>

</div>
</body>
</html>

@php
if (!function_exists('terbilang')) {
function terbilang(int $n): string {
    if ($n < 0) return 'minus ' . terbilang(-$n);
    $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
               'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
               'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
    if ($n < 20)  return $satuan[$n];
    if ($n < 100) return $satuan[(int)($n / 10)] . ' puluh' . ($n % 10 ? ' ' . $satuan[$n % 10] : '');
    if ($n < 200) return 'seratus' . ($n % 100 ? ' ' . terbilang($n % 100) : '');
    if ($n < 1000) return $satuan[(int)($n / 100)] . ' ratus' . ($n % 100 ? ' ' . terbilang($n % 100) : '');
    if ($n < 2000) return 'seribu' . ($n % 1000 ? ' ' . terbilang($n % 1000) : '');
    if ($n < 1000000) return terbilang((int)($n / 1000)) . ' ribu' . ($n % 1000 ? ' ' . terbilang($n % 1000) : '');
    if ($n < 1000000000) return terbilang((int)($n / 1000000)) . ' juta' . ($n % 1000000 ? ' ' . terbilang($n % 1000000) : '');
    return terbilang((int)($n / 1000000000)) . ' miliar' . ($n % 1000000000 ? ' ' . terbilang($n % 1000000000) : '');
}
}
@endphp
