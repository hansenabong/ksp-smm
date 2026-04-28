<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perjanjian Kredit {{ $nomorPk }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.5;
        }
        .page { padding: 1.5cm 2cm 1.5cm 2cm; }
        .header { text-align: center; margin-bottom: 14pt; }
        .header h1 { font-size: 14pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; text-transform: uppercase; }
        .header .nomor { font-size: 11pt; margin-top: 4pt; }
        .opening { text-align: justify; margin-bottom: 8pt; }
        .party { margin-bottom: 8pt; }
        .party-num { float: left; width: 28pt; font-weight: bold; }
        .party-content { margin-left: 28pt; text-align: justify; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .selanjutnya { text-align: justify; margin: 8pt 0; }

        .pasal-block { margin-top: 12pt; }
        .pasal-header { text-align: center; font-weight: bold; margin-bottom: 2pt; }
        .pasal-title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 6pt; }
        .pasal-body { text-align: justify; margin-bottom: 6pt; }
        .pasal-item { margin-bottom: 5pt; text-align: justify; }
        .pasal-item-num { float: left; width: 20pt; }
        .pasal-item-text { margin-left: 20pt; }

        .detail-table { width: 100%; margin: 4pt 0; border-collapse: collapse; }
        .detail-table td { padding: 1pt 4pt; vertical-align: top; }
        .detail-table td:first-child { width: 30%; }
        .detail-table td:nth-child(2) { width: 4%; text-align: center; }

        .signature-section { margin-top: 20pt; }
        .sig-top { width: 100%; border-collapse: collapse; }
        .sig-top td { width: 50%; vertical-align: top; padding: 0 6pt 0 0; }
        .sig-name { margin-top: 60pt; }
        .sig-bottom { width: 100%; border-collapse: collapse; margin-top: 20pt; }
        .sig-bottom td { width: 50%; vertical-align: top; padding: 0 6pt 0 0; }
        .footer-note { margin-top: 16pt; font-size: 9pt; color: #444; border-top: 1px solid #aaa; padding-top: 6pt; text-align: center; }

        .bank-box { border: 1pt solid #000; padding: 6pt 10pt; margin: 6pt 0; display: inline-block; min-width: 200pt; }
    </style>
</head>
<body>
<div class="page">

    @php
        $fd       = array_merge($application->form_data ?? [], $application->doc_overrides ?? []);
        $member   = $application->member;
        $disbDate = $loan?->disbursed_date ?? $application->trx_date;

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];

        $hariId = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $hariStr = $hariId[$disbDate->dayOfWeek];

        $tglStr     = $disbDate->day . ' ' . $bulanId[$disbDate->month] . ' ' . $disbDate->year;
        $tglLunas   = $disbDate->copy()->addMonths($application->tenor_months ?? $loan?->tenor_months ?? 0);
        $tglLunasStr = $tglLunas->day . ' ' . $bulanId[$tglLunas->month] . ' ' . $tglLunas->year;

        $principal    = $loan?->principal                ?? $application->principal;
        $tenor        = $loan?->tenor_months             ?? $application->tenor_months;
        $bunga        = $loan?->interest_monthly_percent ?? $application->interest_monthly_percent;
        $bungaTahunan = $bunga * 12;
        $angsuran     = $loan?->monthly_installment      ?? 0;
        $dueDay       = $disbDate->day;

        $tujuan   = $fd['tujuan_pembiayaan'] ?? $application->notes ?? '';

        // Provisi: per-application override first, then product default
        $provisiPct = filled($fd['provisi_pct'] ?? null)
            ? (float) $fd['provisi_pct']
            : ($application->loanProduct?->provision_rate_percent ?? 7);
        $provisiAmt = round($principal * $provisiPct / 100);

        // Debitur data
        $namaDebitur = $fd['nama_lengkap'] ?? $member?->name ?? '___________________________';
        $pekerjaan   = $fd['pekerjaan']    ?? '___________________________';
        $jalan       = $fd['jalan']        ?? $fd['alamat'] ?? $member?->address ?? '___________________________';
        $rt          = $fd['rt']           ?? '000';
        $rw          = $fd['rw']           ?? '000';
        $noKtp       = $fd['no_ktp']       ?? '___________________________';
        $kelurahan   = $fd['kelurahan']    ?? '___________________________';
        $kecamatan   = $fd['kecamatan']    ?? '___________________________';
        $kota        = $fd['kota']         ?? '___________________________';
        $namaIstri   = $fd['nama_pasangan'] ?? $fd['nama_suami'] ?? '___________________________';
        $alamatIstri = $fd['alamat_pasangan'] ?? '___________________________';
        $rtIstri     = $fd['rt_pasangan']  ?? '000';
        $rwIstri     = $fd['rw_pasangan']  ?? '000';

        // Property / collateral data
        $noHak       = $fd['no_hak']              ?? 'XXXX';
        $namaKel     = $fd['kelurahan_agunan']     ?? 'Kelurahan';
        $tglSuratUkur = $fd['tgl_surat_ukur']     ?? 'XX-XX-XXXX';
        $noSuratUkur  = $fd['no_surat_ukur']      ?? 'XXX/XX/XX';
        $nib          = $fd['nib_agunan']          ?? 'XX.xx.xx.xx';
        $nop          = $fd['nop_agunan']          ?? 'Xx.XX.XXX.XXX.XXX.XXX.X';
        $provinsi     = $fd['provinsi_agunan']     ?? '........................';
        $kabAgunan    = $fd['kota_agunan']         ?? '...........................';
        $kecAgunan    = $fd['kecamatan_agunan']    ?? '............................';
        $kelAgunan    = $fd['kelurahan_agunan']    ?? '....................';
        $jalanAgunan  = $fd['jalan_agunan']        ?? '..........................';
        $atasNama     = $fd['atas_nama_agunan']    ?? '....................................................';
    @endphp

    @php
        $__logoSrc = '';
        foreach ([public_path('images/logo-ksp.png'), base_path('ksp.png'), public_path('images/logo-ksp.jpeg')] as $__f) {
            if (!file_exists($__f)) continue;
            if (str_ends_with($__f, '.png')) {
                $__logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($__f));
            } else {
                $__gd = @imagecreatefromjpeg($__f);
                if ($__gd) { ob_start(); imagepng($__gd); $__p = ob_get_clean(); imagedestroy($__gd); $__logoSrc = 'data:image/png;base64,' . base64_encode($__p); }
                else { $__logoSrc = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($__f)); }
            }
            break;
        }
    @endphp

    {{-- ===== KOP SURAT ===== --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:0;">
        <tr>
            <td style="width:70pt; vertical-align:middle; text-align:left; padding-right:8pt;">
                @if($__logoSrc)
                <img src="{{ $__logoSrc }}" alt="KSP SMM" style="width:62pt; height:62pt;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div style="font-size:15pt; font-weight:bold; letter-spacing:1px; text-transform:uppercase;">KSP SARANA MITRA MANDIRI</div>
                <div style="font-size:9pt; margin-top:3pt;">Jalan RS Fatmawati Komplek Duta Mas Blok B1 No.16, Kel. Cipete Utara</div>
                <div style="font-size:9pt;">Kec. Kebayoran Baru, Kota Jakarta Selatan 12150</div>
                <div style="font-size:9pt;">No. Telepon : 0812 1635 217</div>
            </td>
            <td style="width:70pt;"></td>
        </tr>
    </table>
    <div style="border-top:2pt solid #000; border-bottom:1pt solid #000; height:3pt; margin:6pt 0 12pt 0;"></div>

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <h1>PERJANJIAN KREDIT</h1>
        <div class="nomor">Nomor {{ $nomorPk }}</div>
    </div>

    {{-- ===== PEMBUKA ===== --}}
    <p class="opening">
        Pada hari ini, {{ $hariStr }}, tanggal {{ $disbDate->format('d') }}
        Bulan {{ $bulanId[$disbDate->month] }}
        Tahun Dua Ribu {{ terbilang($disbDate->year - 2000) }}
        ( {{ terbilang($disbDate->year) }} )
        diadakan Perjanjian Kredit yang kemudian disebut "perjanjian" antara :
    </p>

    {{-- Pihak Pertama --}}
    <div class="party clearfix">
        <div class="party-num">I.</div>
        <div class="party-content">
            Koperasi Sarana Mitra Mandiri, suatu perusahaan pembiayaan yang berbentuk Koperasi
            Simpan Pinjam berkedudukan di Jakarta, dalam hal ini diwakili oleh Go Tji Seng,
            dalam kedudukannya sebagai Ketua Koperasi, sah mewakili Koperasi.
            selanjutnya disebut sebagai <strong>Pihak Pertama (Koperasi)</strong>.
        </div>
    </div>

    {{-- Pihak Kedua --}}
    <div class="party clearfix">
        <div class="party-num">II.</div>
        <div class="party-content">
            <strong>{{ $namaDebitur }}</strong>, pekerjaan <strong>{{ $pekerjaan }}</strong>,
            bertempat tinggal di Jalan {{ $jalan }}
            Rukun Tetangga {{ $rt }}, Rukun Warga {{ $rw }},
            pemegang Kartu Tanda Penduduk Nomor <strong>{{ $noKtp }}</strong>,
            Desa/Kelurahan {{ $kelurahan }}, Kecamatan {{ $kecamatan }}, Kota {{ $kota }},
            yang menurut keterangannya bertindak untuk dan atas nama <strong>DIRI SENDIRI</strong>
            dan dalam melakukan tindakan hukum dalam perjanjian ini telah mendapat persetujuan dari
            {{ $namaIstri }} bertempat tinggal di {{ $alamatIstri }}
            RT {{ $rtIstri }} RW {{ $rwIstri }},
            dalam hal ini berkedudukan sebagai istri yang turut serta menandatangani perjanjian ini
            sebagai bukti persetujuannya, untuk selanjutnya disebut <strong>Pihak Kedua (Debitur)</strong>.
        </div>
    </div>

    <p class="selanjutnya">
        Selanjutnya Pihak Pertama (Koperasi) dan Pihak Kedua (Debitur) disebut <strong>"Para Pihak"</strong>.
    </p>
    <p class="opening">
        Sebelum menandatangani Perjanjian ini, Para Pihak bertindak sebagaimana tersebut diatas dan
        menerangkan dengan ini telah mufakat dan setuju untuk membuat dan menandatangani Perjanjian
        dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut :
    </p>

    {{-- ===== PASAL 1 ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 1</div>
    <div class="pasal-title">Jumlah dan Tujuan Penggunaan Fasilitas Kredit</div>
    <p class="pasal-body">
        Pihak Pertama (Koperasi) bersedia memberikan Fasilitas Kredit sebesar
        Rp {{ number_format($principal, 0, ',', '.') }},
        ({{ terbilang((int)$principal) }} rupiah) yang terdiri dari :
    </p>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Fasilitas Pinjaman Berjangka dengan Plafond sebesar
            Rp {{ number_format($principal, 0, ',', '.') }},- ({{ terbilang((int)$principal) }})
            dengan jangka waktu {{ $tenor }} ({{ terbilang($tenor) }}) bulan,
            terhitung sejak tanggal {{ $tglStr }} ({{ terbilang($disbDate->day) }})
            dan karenanya seluruh hutang harus dibayar lunas oleh Pihak Kedua (Debitur) kepada
            Pihak Pertama (Koperasi) pada tanggal {{ $tglLunasStr }};
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Pelunasan mana meliputi hutang pokok, bunga, biaya administrasi dan biaya-biaya lainnya.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) dengan ini mengaku telah mendapat Fasilitas Kredit yang sah karena
            uang yang telah dipinjam itu untuk kepentingannya dan telah menerima lengkap dari Pihak
            Pertama (Koperasi), untuk penerimaan surat Perjanjian ini berlaku sebagai tanda penerimaan
            yang sah.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Kredit ini hanya boleh dipergunakan untuk tujuan
            "<strong>{{ $tujuan ?: '___________________________' }}</strong>"
            yang akan dipergunakan oleh Pihak Kedua (Debitur) pada waktu dan untuk keperluan sesuai
            dengan rencana yang telah disetujui oleh Pihak Pertama (Koperasi).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">e.</div>
        <div class="pasal-item-text">
            Setiap waktu Pihak Pertama (Koperasi) dan/atau Pihak Ketiga yang ditunjuk oleh Pihak
            Pertama (Koperasi) berhak memeriksa penggunaan kredit yang dimaksud, satu dan lain dalam
            rangka pengawasan dan pengamanan kredit tersebut dan Pihak Kedua (Debitur) wajib membayar
            seluruh pinjaman beserta bunga dan ongkos-ongkos lain yang timbul karena perjanjian ini,
            pada seketika itu juga dengan syarat, apabila Pihak Kedua (Debitur) tidak membayarnya
            maka Pihak Pertama (Koperasi) berkuasa untuk menjual barang atau benda yang dijadikan
            jaminan atau agunan dalam perjanjian ini baik di hadapan umum maupun di bawah tangan.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 2 ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 2</div>
    <div class="pasal-title">Bunga, Administrasi, dan Penalty</div>
    <p class="pasal-body">
        Terhadap Fasilitas Kredit yang diberikan oleh Pihak Pertama (Koperasi) tersebut kepada
        Pihak Kedua (Debitur) dibebankan :
    </p>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            <strong>Bunga</strong> : untuk Fasilitas Kredit kepada Pihak kedua (Debitur) dibebankan
            suku bunga pinjaman sebesar <strong>{{ number_format($bungaTahunan, 0) }}%
            ({{ terbilang((int)$bungaTahunan) }} persen)</strong> pertahun flat untuk masa
            60 (enam puluh) bulan atas seluruh jumlah Fasilitas Kredit yang diberikan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            <strong>Biaya Administrasi</strong> : untuk Fasilitas Kredit dikenakan biaya sebesar
            Rp 20.000.000,- (Dua Puluh Juta Rupiah) perakad untuk setiap masa 60 (enam puluh) bulan
            atas seluruh jumlah Fasilitas Kredit yang diberikan dan dipungut terlebih dahulu untuk
            setiap masa tersebut.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            <strong>Biaya Provisi</strong> : untuk Fasilitas Kredit kepada Pihak Kedua (Debitur)
            dikenakan biaya provisi sebesar <strong>{{ number_format($provisiPct, 0) }}%</strong>
            perakad untuk setiap masa 60 (enam puluh) bulan atas seluruh jumlah Fasilitas Kredit
            yang diberikan dan dipungut terlebih dahulu untuk setiap masa tersebut.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) dengan ini menyatakan persetujuannya, apabila dalam batas waktu
            berlakunya kredit ini belum melunasi secara seksama dan sepatutnya seluruh jumlah kredit
            yang dipergunakan berdasarkan Perjanjian ini beserta bunganya maka Pihak Pertama (Koperasi)
            berhak memperhitungkan denda (Penalty overdue) terhadap Pihak Kedua (Debitur) yang besarnya
            akan ditentukan oleh Pihak Pertama (Koperasi) sebagaimana yang tercantum pada pasal 3.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 3 ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 3</div>
    <div class="pasal-title">Batas Waktu Penarikan Dana/atau Penggunaan Fasilitas Kredit</div>
    <p class="pasal-body">Untuk Fasilitas Kredit berlaku ketentuan sebagai berikut :</p>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Pembayaran Angsuran Pokok Pinjaman dan Bunga Pinjaman akan dilakukan dengan pendebetan
            langsung oleh Pihak Pertama (Koperasi) dari rekening tabungan
            ({{ strtoupper($namaDebitur) }}), pada Koperasi Simpan Pinjam Sarana Mitra Mandiri,
            setiap tanggal <strong>{{ $dueDay }}</strong>, tiap-tiap bulannya sebesar
            <strong>Rp {{ number_format($angsuran, 0, ',', '.') }},-
            ({{ terbilang((int)$angsuran) }})</strong>
            yang pertama kalinya pada tanggal {{ $tglStr }}
            dan harus sudah lunas seluruhnya selambat-lambatnya pada tanggal {{ $tglLunasStr }}.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Apabila Pihak Kedua (Debitur) melunasi seluruh pinjamannya kepada Pihak Pertama
            (Koperasi) sebelum tanggal jatuh tempo maka Pihak Kedua (Debitur) dikenakan denda
            penalty sebesar <strong>5 % (lima) persen</strong> dari sisa outstanding/baki debet.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Besarnya jumlah angsuran pokok pinjaman dan bunga pinjaman adalah sesuai dengan daftar
            pembayaran yang telah ditetapkan.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Apabila Pihak Kedua (Debitur) melakukan kelalaian untuk setiap bulannya tidak melakukan
            pembayaran angsuran pokok pinjaman dan bunga pinjaman, maka Pihak Kedua (Debitur) akan
            dikenakan bunga pinjaman yang besarnya ditentukan oleh Pihak Pertama (Koperasi) atas
            seluruh pinjaman yang masih ada dan Pihak Kedua (Debitur) wajib membayar seluruh
            kewajiban pinjaman kepada Pihak Pertama (Koperasi) secara seketika dan sekaligus lunas
            tanpa pemberitahuan atau surat-surat lain semacam itu. Jika Pihak kedua (Debitur) lalai
            dalam memenuhi kewajiban seperti tersebut di atas, maka Pihak Pertama (Koperasi) berhak
            untuk melakukan eksekusi atas jaminan pinjaman dari Fasilitas Kredit Pihak Kedua (Debitur)
            atau tindakan-tindakan hukum lainnya yang dianggap baik oleh Pihak Pertama (Koperasi).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">e.</div>
        <div class="pasal-item-text">
            Apabila Pihak kedua (Debitur) melakukan keterlambatan atas pembayaran Angsuran Pokok
            Pinjaman dan Angsuran Bunga Pinjaman pada setiap bulannya, maka Pihak Kedua (Debitur)
            akan dikenakan denda oleh Pihak Pertama (Koperasi) sebesar
            <strong>0,3 % (nol koma tiga) persen per hari</strong>.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 4 — AGUNAN (SERTIPIKAT) ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 4</div>
    <div class="pasal-title">Agunan dan/atau Jaminan</div>
    <p class="pasal-body">
        Untuk menambah kepastian tentang pembayaran kembali yang sepatutnya dari pada kredit
        tersebut diatas dan pelunasan yang seksama dari bunga dan biaya-biaya lainnya, Pihak Kedua
        (Debitur) memberikan jaminan kebendaan dengan segala pembebasan (vrijwaring) beban, apapun
        sifatnya kepada Pihak Pertama (Koperasi), berupa Tanah dan Bangunan sebagai berikut :
    </p>
    <p class="pasal-body" style="margin-left: 20pt;">
        Hak Milik, Nomor {{ $noHak }}/{{ $namaKel }},
        sebagaimana yang telah diuraikan dalam Surat Ukur tertanggal {{ $tglSuratUkur }}
        Nomor {{ $noSuratUkur }},
        dengan Nomor Identifikasi Bidang Tanah (NIB) : {{ $nib }};
        dan Surat Pemberitahuan Pajak Terhutang Pajak Bumi dan Bangunan (SPPT PBB)
        Nomor Objek Pajak (NOP) : {{ $nop }};
        yang terletak di :
    </p>
    <table class="detail-table" style="margin-left: 20pt; width: auto; min-width: 280pt;">
        <tr><td>Provinsi</td><td>:</td><td><strong>{{ $provinsi }}</strong></td></tr>
        <tr><td>Kabupaten</td><td>:</td><td><strong>{{ $kabAgunan }}</strong></td></tr>
        <tr><td>Kecamatan</td><td>:</td><td><strong>{{ $kecAgunan }}</strong></td></tr>
        <tr><td>Kelurahan</td><td>:</td><td><strong>{{ $kelAgunan }}</strong></td></tr>
        <tr><td>Jalan</td><td>:</td><td><strong>{{ $jalanAgunan }}</strong></td></tr>
    </table>
    <p class="pasal-body" style="margin-top: 6pt; margin-left: 20pt;">
        Sertifikat terdaftar atas nama <strong>{{ $atasNama }}</strong>;
    </p>
    <p class="pasal-body">
        Hanya Pihak Pertama (Koperasi) yang dapat mempertimbangkan bila jaminan tersebut harus
        ditambah atau dapat dikurangi.
    </p>
    </div>

    {{-- ===== PASAL 5 — ASURANSI ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 5</div>
    <div class="pasal-title">Asuransi</div>
    <p class="pasal-body">
        Selama Pihak Kedua (Debitur) masih berhutang kepada Pihak Pertama (Koperasi) maka
        Pihak Kedua (Debitur) diwajibkan mengikuti Asuransi KEMATIAN, KEBAKARAN, dan lain
        sebagainya sesuai yang telah ditentukan dan ditetapkan oleh Pihak Pertama (Koperasi),
        dan Pihak Pertama (Koperasi) berhak menerima uang ganti rugi kerugian tersebut, dan uang
        ganti rugi dari perusahaan asuransi bersangkutan akan dipergunakan untuk melunasi hutang
        Pihak Kedua (Debitur) kepada Pihak Pertama (Koperasi), berikut dengan bunga-bunga dan
        lain-lain serta biaya-biaya yang bertalian dengan hutang itu, tidak ada yang dikecualikan
        dengan ketentuan, bahwa apabila uang ganti kerugian itu tidak cukup untuk melunasi hutang
        Pihak Kedua (Debitur) kepada Pihak Pertama (Koperasi), maka Pihak Kedua (Debitur)
        diwajibkan untuk menambah kekurangannya dan sebaliknya apabila ada kelebihan maka kelebihan
        itu akan diserahkan oleh Pihak Pertama (Koperasi) kepada Pihak Kedua (Debitur), untuk
        kelebihan mana maka Pihak Kedua (Debitur) tidak berhak untuk mendapatkan bunga dari padanya
        sedangkan premi-premi asuransi tersebut dan polis asli asuransi tersebut selama
        berlangsungnya perjanjian kredit ini akan disimpan oleh Pihak Pertama (Koperasi).
    </p>
    </div>

    {{-- ===== PASAL 6 — KEJADIAN KELALAIAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 6</div>
    <div class="pasal-title">Kejadian Kelalaian</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Apabila Pihak kedua (Debitur) berada dalam keadaan lalai atau berhenti membayar seperti
            yang tercantum pada pasal 3 ayat 1 huruf (d), maka Pihak Pertama (Koperasi) tanpa perlu
            mendapat persetujuan lebih dahulu dari Pihak Kedua (Debitur). Pihak Pertama (Koperasi)
            berhak untuk menjual dan/atau memindahtangankan, atau dengan cara cessie/pengalihan
            piutang dan/atau menjual secara lelang serta berhak untuk mengambil langkah-langkah
            hukum lainnya atas barang jaminan baik di muka umum maupun di bawah tangan untuk
            sejumlah harga dan syarat-syarat yang ditimbang oleh Pihak Pertama (Koperasi).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Hasil bersih dari penjualan jaminan tersebut, yaitu setelah dikurangi biaya-biaya yang
            diperlukan untuk tindakan tersebut, akan dipergunakan untuk melunasi seluruh hutang
            Pihak Kedua (Debitur).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Sedangkan apabila dari hasil penjualan jaminan ternyata tidak mencukupi atau tidak dapat
            melunasi seluruh hutang pokok, hutang bunga, biaya provisi, maupun biaya-biaya lain yang
            timbul berdasarkan perjanjian ini termasuk perubahannya dan/atau penambahannya dan/atau
            pembaharuannya dan/atau perpanjangannya yang mungkin ada dan/atau perjanjian-perjanjian
            apapun, baik yang telah ada dan/atau akan dibuat, maka sisa hutang tersebut tetap menjadi
            kewajiban Pihak kedua (Debitur) untuk membayar dan melunasinya.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 7 — BERAKHIRNYA PERJANJIAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 7</div>
    <div class="pasal-title">Berakhirnya Perjanjian</div>
    <p class="pasal-body">
        Menyimpang dari apa yang ditentukan dalam pasal 1 ayat 4 tersebut di atas, jika terjadi
        salah satu sebab yang tersebut di bawah ini, maka Pihak Pertama (Koperasi) berhak untuk
        seketika tanpa somasi lagi mengakhiri perjanjian ini dan menuntut pembayaran dengan seketika
        dan sekaligus lunas dari jumlah uang yang terhutang oleh Pihak Kedua (Debitur) berdasarkan
        perjanjian ini termasuk perubahannya dan/atau penambahannya dan/atau pembaharuannya dan/atau
        perjanjian-perjanjian apapun, baik yang telah ada dan/atau akan dilunasinya, baik karena
        hutang pokok, bunga-bunga, biaya administrasi, provisi, dan biaya-biaya lain sehubungan
        dengan hutang dimaksud dan oleh karena itu pemberitahuan dengan surat juru sita atau
        surat-surat lain serupa itu tidak diperlukan lagi bilamana :
    </p>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) tidak atau tidak cukup melaksanakan salah satu kewajibannya yang
            ditentukan dalam perjanjian ini atau kemudian akan diberlakukan oleh Pihak Pertama
            (Koperasi), maka Pihak Kedua (Debitur) telah berada dalam keadaan lalai, kelalaian mana
            cukup dibuktikan dengan tidak dilaksanakannya kewajiban secara layak dari pada waktunya;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">Pihak Kedua (Debitur) meninggal dunia;</div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) jatuh pailit dan/atau karena apapun juga kehilangan haknya untuk
            mengurus sendiri harta kekayaannya;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) mendapat pengunduran pembayaran (surseance van betaling);
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">e.</div>
        <div class="pasal-item-text">
            Jaminan sebagaimana pada pasal 4 ayat (1) terindikasi sengketa, dijual, diubah sehingga
            tidak seperti jaminan semula atau dipindahtangankan, disewakan atau dengan cara lain
            dilepaskan atau dijaminkan oleh Pihak Kedua (Debitur) kepada pihak lain;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">f.</div>
        <div class="pasal-item-text">Harta kekayaan Pihak Kedua (Debitur) dikenakan sitaan;</div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">g.</div>
        <div class="pasal-item-text">
            Likuiditas, Bonafiditas, dan Solvabilitas Pihak Kedua (Debitur) sedemikian rupa, sehingga
            menurut pertimbangan Pihak Pertama (Koperasi), Pihak Kedua (Debitur) tidak dapat membayar
            hutangnya lagi;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">h.</div>
        <div class="pasal-item-text">
            Izin usaha Pihak Kedua (Debitur) dicabut, baik untuk sementara maupun selamanya, apa yang
            dijaminkan dalam perjanjian ini disita pihak lain dan/atau adalah merubah sedemikian rupa
            jaminan sehingga harganya menurut Pihak pertama (Koperasi) tidak memberikan jaminan yang
            cukup guna pembayaran lunas hutang Pihak Kedua (Debitur);
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">i.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) tidak memenuhi salah satu atau lebih kewajiban sebagaimana
            ditentukan dalam perjanjian ini;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">j.</div>
        <div class="pasal-item-text">
            Terjadi peristiwa yang berada di luar kekuasaan dan kemampuan Pihak Pertama (Koperasi)
            untuk mengatasinya (force majeur) antara lain akibat krisis atau kemacetan likuiditas
            sebagai akibat dari perubahan kebijaksanaan pemerintah dibidang moneter;
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">k.</div>
        <div class="pasal-item-text">
            Kekayaan Pihak Kedua (Debitur) menjadi berkurang sedemikian rupa, sehingga tidak lagi
            merupakan jaminan yang cukup bagi hutangnya pada Pihak Pertama (Koperasi);
        </div>
    </div>
    </div>

    {{-- ===== PASAL 8 — PERNYATAAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 8</div>
    <div class="pasal-title">Pernyataan</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) menyetujui bahwa pembukuan Pihak Pertama (Koperasi) selalu menjadi
            dasar untuk menetapkan jumlah-jumlah yang terhutang oleh Pihak Kedua (Debitur) pada
            Pihak Pertama (Koperasi) dan Pihak Kedua (Debitur) melepaskan haknya untuk mengajukan
            keberatan atas pembuktian tersebut.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) memberi hak dan kuasa penuh kepada Pihak Pertama (Koperasi) untuk
            membebani atau mendebet rekening Pihak Kedua (Debitur) pada Pihak Pertama (Koperasi),
            guna pembayaran-pembayaran bunga pinjaman angsuran/cicilan pokok pinjaman, provisi, biaya
            administrasi dan biaya-biaya lainnya yang wajib dilaksanakan/dilakukan oleh Pihak Kedua
            (Debitur) kepada Pihak Pertama (Koperasi) sehubungan dengan adanya perjanjian ini.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) mengikat diri terhadap Pihak Pertama (Koperasi) untuk menyampaikan
            informasi-informasi lain apapun, memberitahukan tentang setiap keadaan mengenai sesuatu
            perubahan besar dalam keuangan, dan Pihak Kedua (Debitur) tidak akan mengikat diri sebagai
            penanggung (borg) terhadap Pihak Ketiga serta tunduk sepenuhnya atas segala petunjuk yang
            ditetapkan dan/atau yang kemudian hari akan ditetapkan oleh Pihak Pertama (Koperasi).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) tidak ada suatu perkara perdata, tata usaha negara, tuntutan pajak,
            penyidikan maupun perkara pidana atau sengketa yang sedang berlangsung, yang mengancam
            atau dapat menimbulkan akibat terhadap Pihak Kedua (Debitur), sehingga mempengaruhi
            keadaan keuangan atau usaha-usaha Pihak Kedua (Debitur) atau dapat mengganggu kemampuan
            Pihak Kedua (Debitur) untuk melaksanakan kewajibannya berdasarkan Perjanjian Kredit.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">e.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) wajib memberitahukan kepada Pihak Pertama (Koperasi) tentang adanya
            permohonan pernyataan pailit yang diajukan oleh Krediturnya atau pihak lain kepada
            Pengadilan Niaga untuk menyatakan pailit Pihak Kedua (Debitur) selambat-lambatnya
            3 (tiga) hari sejak Pihak Kedua (Debitur) mengetahui adanya permohonan pernyataan pailit
            dimaksud atau sejak Pihak Kedua (Debitur) menerima panggilan sidang dari Pengadilan Niaga
            atas permohonan pernyataan pailit dimaksud.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">f.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) memberikan semua dokumen, data, dan keterangan kepada Pihak Pertama
            (Koperasi) adalah yang sebenarnya dan dapat dipertanggungjawabkan keasliannya.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">g.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) dengan ini menyatakan persetujuannya dalam hal terjadi salah satu
            kelalaian atas Pihak Kedua (Debitur) dan/atau yang telah menyatakan persetujuannya
            sebagaimana pada nomor II romawi diatas maka Pihak Pertama (Koperasi) berhak untuk
            menuntut pembayaran atas jumlah terhutang oleh Pihak Kedua (Debitur) berdasarkan
            Perjanjian Kredit termasuk perubahannya dan/atau penambahannya dan/atau pembaharuannya
            dan/atau perpanjangannya dengan menggunakan jasa Pihak Ketiga yang ditunjuk oleh Pihak
            Pertama (Koperasi).
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">h.</div>
        <div class="pasal-item-text">
            Pihak kedua (Debitur) dengan ini menyatakan persetujuannya apabila jaminan disita oleh
            suatu instansi pemerintah akibat dari suatu tindak pidana dan/atau kejahatan lainnya,
            Pihak Pertama (Koperasi) berhak mengambil alih dan menguasai jaminan tersebut dan tanpa
            perlu mendapat persetujuan lebih dulu dari Pihak Kedua (Debitur), Pihak Pertama (Koperasi)
            berhak menjual dan/atau memindahtangankan, atau dengan cara menjual secara lelang serta
            berhak untuk mengambil langkah-langkah hukum lainnya atas jaminan baik di muka umum
            maupun di bawah tangan untuk sejumlah harga dan syarat-syarat yang ditimbang oleh Pihak
            Pertama (Koperasi). Hasil bersih dari penjualan jaminan tersebut, yaitu setelah dikurangi
            biaya-biaya yang diperlukan untuk tindakan tersebut, akan dipergunakan untuk melunasi
            seluruh hutang Pihak Kedua (Debitur).
        </div>
    </div>
    </div>

    {{-- ===== PASAL 9 — LARANGAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 9</div>
    <div class="pasal-title">Larangan Bagi Pihak Kedua (Debitur)</div>
    <p class="pasal-body">
        Selama Pihak Kedua (Debitur) belum membayar lunas hutang dan/atau penggunaan Fasilitas Kredit
        belum berakhir, Pihak Kedua (Debitur) dilarang atau tidak diperkenankan untuk melakukan
        hal-hal di bawah ini, tanpa persetujuan tertulis dahulu dari Pihak Pertama (Koperasi) :
    </p>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) mengajukan permohonan pernyataan pailit kepada Pengadilan Niaga
            untuk menyatakan pailit dari Pihak Kedua (Debitur) sendiri.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) mengalihkan, menjual, menyewakan jaminan kepada Pihak Ketiga atau
            pihak lain.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) menggunakan jaminan untuk suatu alat tindak pidana atau suatu alat
            yang digunakan untuk melanggar ketentuan Peraturan Perundang-Undangan yang berlaku.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) merubah bentuk jaminan sehingga tidak seperti bentuk awal jaminan.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 10 — TEMPAT PEMBAYARAN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 10</div>
    <div class="pasal-title">Tempat Pembayaran</div>
    <p class="pasal-body">
        Pembayaran dapat dilakukan kepada dan di Kantor "Pihak Pertama" yaitu Koperasi Simpan Pinjam
        Sarana Mitra Mandiri, Komplek Duta Mas Fatmawati Blok B1 No 16 Jakarta Selatan 12150 atau
        di alamat yang ditunjuk oleh Pihak Pertama (Koperasi) atau :
    </p>
    <div class="bank-box">
        <table style="border-collapse:collapse; font-size:10pt;">
            <tr><td style="padding:2pt 4pt; width:120pt;">Bank</td><td>:</td><td style="padding:2pt 4pt;">.........</td></tr>
            <tr><td style="padding:2pt 4pt;">KCP</td><td>:</td><td style="padding:2pt 4pt;">..................................</td></tr>
            <tr><td style="padding:2pt 4pt;">A/C No.</td><td>:</td><td style="padding:2pt 4pt;">....................</td></tr>
            <tr><td style="padding:2pt 4pt;">Atas Nama</td><td>:</td><td style="padding:2pt 4pt;"><strong>KSP SARANA MITRA MANDIRI</strong></td></tr>
            <tr><td style="padding:2pt 4pt;">Cabang</td><td>:</td><td style="padding:2pt 4pt;">..................................</td></tr>
        </table>
    </div>
    </div>

    {{-- ===== PASAL 11 — PENYERAHAN DAN PENGEMBALIAN DOKUMEN/SERTIPIKAT ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 11</div>
    <div class="pasal-title">Penyerahan dan Pengembalian Dokumen/Sertipikat</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Pihak Kedua (Debitur) wajib menyerahkan semua bukti-bukti kepemilikan obyek pembiayaan
            yang dibiayai dengan Fasilitas Kredit ini kepada Pihak Pertama (Koperasi) atau Pihak
            Ketiga yang ditunjuk oleh Pihak Pertama (Koperasi) untuk disimpan selama hutang belum
            dibayar lunas.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Dalam hal Pihak Kedua (Debitur) telah melunasi seluruh hutangnya kepada Pihak Pertama
            (Koperasi), maka Pihak Pertama (Koperasi) berkewajiban untuk mengembalikan semua
            bukti-bukti kepemilikan obyek pembiayaan kepada Pihak Kedua (Debitur) paling lambat
            14 (empat belas) hari kerja setelah proses pembebanan hak tanggungan pada Badan
            Pertanahan Nasional selesai.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 12 — KETENTUAN LAIN ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 12</div>
    <div class="pasal-title">Ketentuan Lain</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Jumlah hutang oleh Pihak Kedua (Debitur) kepada Pihak Pertama (Koperasi) atau
            pengganti-pengganti haknya yang merupakan hutang yang tidak dapat dibagi-bagi
            (Ondeelbare schuld) sehingga berdasarkan pasal 1300 Kitab Undang-Undang Hukum Perdata,
            setiap pengganti hak dari Pihak Kedua (Debitur) dapat dituntut untuk seluruh jumlah
            hutang.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Semua kuasa yang termaktub di dalam perjanjian ini merupakan bagian yang terpenting yang
            tidak dapat dipisahkan dari perjanjian ini, yang tidak akan dibuat tanpa adanya kuasa
            tersebut dan oleh karena itu kuasa-kuasa tersebut tidak dapat dicabut dan tidak akan
            berakhir karena sebab apapun juga atau karena sebab-sebab yang diatur dalam pasal 1813
            Kitab Undang-Undang Hukum Perdata.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">c.</div>
        <div class="pasal-item-text">
            Semua surat menyurat atau pemberitahuan-pemberitahuan yang harus dikirim oleh
            masing-masing pihak kepada pihak lain mengenai atau sehubungan dengan Perjanjian ini
            ke alamat-alamat tersebut dibawah ini :
            <br><br>
            <table style="width:100%; border-collapse:collapse; font-size:10pt;">
                <tr>
                    <td style="width:50%; vertical-align:top;">
                        <strong>Pihak Pertama (Koperasi)</strong><br>
                        Koperasi Simpan Pinjam Sarana Mitra Mandiri<br>
                        Komplek Dutamas Fatmawati Blok B1 No. 16<br>
                        Cipete Utara, Kebayoran Baru, Jakarta Selatan<br>
                        Telp : 021 - ......................................
                    </td>
                    <td style="width:50%; vertical-align:top;">
                        <strong>Pihak Kedua (Debitur)</strong><br>
                        Nama : {{ $namaDebitur }}<br>
                        Alamat : {{ $jalan }},<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $kelurahan }}, {{ $kecamatan }}<br>
                        Telp : ......................................
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">d.</div>
        <div class="pasal-item-text">
            Hal-hal lain yang belum diatur dan atau belum cukup diatur dalam Perjanjian Kredit ini,
            termasuk dan tidak terbatas pada hal-hal mengenai perubahan-perubahannya, akan diatur
            oleh Para Pihak dalam suatu persetujuan tersendiri yang merupakan satu kesatuan dan tidak
            dapat terpisahkan dari Perjanjian ini.
        </div>
    </div>
    </div>

    {{-- ===== PASAL 13 ===== --}}
    <div class="pasal-block">
    <div class="pasal-header">Pasal 13</div>
    <div class="pasal-title">Hukum Yang Dipakai dan Yurisdiksi Pengadilan</div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">a.</div>
        <div class="pasal-item-text">
            Mengenai Perjanjian ini dan segala akibatnya, kedua belah pihak memilih tempat kedudukan
            hukum (domisili) umum yang tetap dan secara hukum pada Kantor Panitera Pengadilan Negeri
            yang berwenang atas wilayah tempat Kantor Pihak Pertama (Koperasi) yang memberi kredit.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">b.</div>
        <div class="pasal-item-text">
            Dengan tidak mengurangi hak dari Pihak pertama (Koperasi) untuk memohon pelaksanaan
            (eksekusi) dari Perjanjian ini atau mengajukan tuntutan hukum terhadap Pihak Kedua
            (Debitur) berdasarkan Perjanjian ini melalui Pengadilan-Pengadilan Negeri lainnya dalam
            Wilayah Republik Indonesia.
        </div>
    </div>
    </div>

    {{-- ===== PENUTUP ===== --}}
    <p class="opening" style="margin-top: 14pt;">
        Demikian Perjanjian Kredit ini dibuat dan telah disesuaikan dengan ketentuan Peraturan
        Perundang-undangan, ditandatangani di Jakarta, pada hari dan tanggal seperti tersebut pada
        bagian awal Perjanjian ini.
    </p>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-section">
        <table class="sig-top">
            <tr>
                <td>
                    <strong>"Pihak Pertama"</strong><br>
                    <strong>KSP SARANA MITRA MANDIRI</strong>
                    <div class="sig-name">
                        <strong>Go Tji seng</strong><br>
                        .....................................................<br>
                        <strong>Ketua Koperasi</strong>
                    </div>
                </td>
                <td>
                    <strong>"Pihak Kedua",</strong>
                    <div class="sig-name">
                        .....................................................<br>
                        <br>
                        <strong>Persetujuan Suami,</strong><br>
                        <br>
                        .....................................................
                    </div>
                </td>
            </tr>
        </table>

        <table class="sig-bottom">
            <tr>
                <td>
                    Saksi,<br>
                    <div style="margin-top: 50pt;">
                        (.........................................................)
                    </div>
                </td>
                <td>
                    Saksi,<br>
                    <div style="margin-top: 50pt;">
                        (.........................................)
                    </div>
                </td>
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
