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

        .page {
            padding: 2cm 2.5cm 2cm 2.5cm;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 18pt;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header .nomor {
            font-size: 11pt;
            margin-top: 4pt;
        }

        /* Divider */
        .divider {
            border-top: 2px solid #000;
            margin: 8pt 0;
        }

        /* Opening paragraph */
        .opening {
            text-align: justify;
            margin-bottom: 10pt;
        }

        /* Party blocks */
        .party {
            margin-bottom: 10pt;
        }
        .party-num {
            float: left;
            width: 28pt;
            font-weight: bold;
        }
        .party-content {
            margin-left: 28pt;
            text-align: justify;
        }
        .clearfix::after { content: ''; display: table; clear: both; }

        /* Selanjutnya line */
        .selanjutnya {
            text-align: justify;
            margin: 10pt 0;
        }

        /* Section header (Pasal) */
        .pasal-header {
            text-align: center;
            font-weight: bold;
            margin-top: 14pt;
            margin-bottom: 2pt;
        }
        .pasal-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8pt;
        }

        /* Pasal content */
        .pasal-item {
            margin-bottom: 6pt;
            text-align: justify;
        }
        .pasal-item-num {
            float: left;
            width: 20pt;
        }
        .pasal-item-text {
            margin-left: 20pt;
        }

        /* Detail table inside pasal */
        .detail-table {
            width: 100%;
            margin: 6pt 0;
            border-collapse: collapse;
        }
        .detail-table td {
            padding: 2pt 4pt;
            vertical-align: top;
        }
        .detail-table td:first-child {
            width: 44%;
        }
        .detail-table td:nth-child(2) {
            width: 4%;
            text-align: center;
        }

        /* Signature section */
        .signature-section {
            margin-top: 24pt;
        }
        .signature-grid {
            width: 100%;
        }
        .signature-grid td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 12pt;
        }
        .signature-box {
            margin-top: 60pt;
            border-top: 1px solid #000;
            padding-top: 4pt;
        }

        /* Footer note */
        .footer-note {
            margin-top: 16pt;
            font-size: 9pt;
            color: #444;
            border-top: 1px solid #aaa;
            padding-top: 6pt;
            text-align: center;
        }

        /* Fill line */
        .fill {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 120pt;
        }
        .fill-long {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 240pt;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <h1>Perjanjian Pembiayaan</h1>
        <div class="nomor">Nomor: {{ $nomorPk }}</div>
    </div>
    <div class="divider"></div>

    {{-- ===== PEMBUKA ===== --}}
    @php
        $fd   = $application->form_data ?? [];
        $member = $application->member;
        $disbDate = $loan?->disbursed_date ?? $application->trx_date;

        // Format tanggal Indonesia
        $bulanId = ['', 'Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
        $tglStr = $disbDate->day . ' ' . $bulanId[$disbDate->month] . ' ' . $disbDate->year;

        // Loan figures
        $principal    = $loan?->principal       ?? $application->principal;
        $tenor        = $loan?->tenor_months    ?? $application->tenor_months;
        $bunga        = $loan?->interest_monthly_percent ?? $application->interest_monthly_percent;
        $angsuran     = $loan?->monthly_installment ?? 0;
        $totalPayable = $loan?->total_payable   ?? 0;
    @endphp

    <p class="opening">
        Pada hari ini, tanggal <strong>{{ $tglStr }}</strong>, diadakan Perjanjian Pembiayaan Konsumen yang kemudian disebut <em>"Perjanjian"</em> antara:
    </p>

    {{-- Pihak Pertama --}}
    <div class="party clearfix">
        <div class="party-num">I.</div>
        <div class="party-content">
            <strong>Koperasi Simpan Pinjam</strong>, suatu badan usaha berbentuk Koperasi yang berkedudukan di tempat sebagaimana tercantum dalam akta pendirian, dalam hal ini diwakili oleh Pengurus Koperasi yang berwenang, selanjutnya disebut <strong>"Pihak Pertama"</strong>.
        </div>
    </div>

    {{-- Pihak Kedua --}}
    <div class="party clearfix">
        <div class="party-num">II.</div>
        <div class="party-content">
            <strong>{{ $fd['nama_lengkap'] ?? $member?->name ?? '___________________________' }}</strong>,
            bertempat tinggal di
            {{ $fd['alamat'] ?? '___________________________' }}
            @if(!empty($fd['rt']) && !empty($fd['rw']))
                RT {{ $fd['rt'] }}/RW {{ $fd['rw'] }},
            @endif
            @if(!empty($fd['kota']))
                {{ $fd['kota'] }}{{ !empty($fd['kode_pos']) ? ' ' . $fd['kode_pos'] : '' }},
            @endif
            pekerjaan <strong>{{ $fd['pekerjaan'] ?? '_______________' }}</strong>,
            pemegang KTP Nomor <strong>{{ $fd['no_ktp'] ?? '___________________________' }}</strong>,
            yang menurut keterangannya bertindak untuk dan atas nama dirinya sendiri
            @if(!empty($fd['nama_pasangan']))
                dengan persetujuan dari {{ ($fd['kelamin'] ?? '') === 'L' ? 'istri' : 'suami' }} bernama <strong>{{ $fd['nama_pasangan'] }}</strong>
            @endif
            , yang turut menandatangani Perjanjian ini, selanjutnya disebut <strong>"Pihak Kedua"</strong>.
        </div>
    </div>

    <p class="selanjutnya">
        Selanjutnya <em>"Pihak Pertama"</em> dan <em>"Pihak Kedua"</em> disebut <strong>"Para Pihak"</strong>.
    </p>

    <p class="opening">
        Sebelum menandatangani <em>"Perjanjian"</em> ini, <em>"Para Pihak"</em> terlebih dahulu menerangkan hal-hal sebagai berikut:
    </p>
    <p class="opening">
        Bahwa <em>"Pihak Kedua"</em> berkeinginan untuk mendapatkan fasilitas dari <em>"Pihak Pertama"</em>;
        sehubungan dengan apa yang telah diuraikan di atas, <em>"Para Pihak"</em> telah sepakat membuat Perjanjian dengan syarat-syarat dan ketentuan sebagai berikut:
    </p>

    {{-- ===== PASAL 1 ===== --}}
    <div class="pasal-header">Pasal 1</div>
    <div class="pasal-title">Plafond dan Jangka Waktu</div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            <em>"Pihak Pertama"</em> memberikan fasilitas pembiayaan kepada <em>"Pihak Kedua"</em> dengan ketentuan sebagai berikut:
            <table class="detail-table">
                <tr>
                    <td>Produk Pembiayaan</td>
                    <td>:</td>
                    <td><strong>{{ $application->loanProduct?->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td>Pokok Pinjaman (Plafond)</td>
                    <td>:</td>
                    <td><strong>Rp {{ number_format($principal, 0, ',', '.') }}</strong>
                        ({{ terbilang($principal) }} rupiah)</td>
                </tr>
                <tr>
                    <td>Jangka Waktu</td>
                    <td>:</td>
                    <td><strong>{{ $tenor }} ({{ terbilang($tenor) }}) bulan</strong></td>
                </tr>
                <tr>
                    <td>Bunga Flat per Bulan</td>
                    <td>:</td>
                    <td><strong>{{ $bunga }}%</strong></td>
                </tr>
                <tr>
                    <td>Angsuran per Bulan</td>
                    <td>:</td>
                    <td><strong>Rp {{ number_format($angsuran, 0, ',', '.') }}</strong>
                        @if($angsuran > 0)
                            ({{ terbilang($angsuran) }} rupiah)
                        @endif
                    </td>
                </tr>
                @if($application->admin_monthly)
                <tr>
                    <td>Biaya Admin per Bulan</td>
                    <td>:</td>
                    <td>Rp {{ number_format($application->admin_monthly, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($application->insurance_monthly)
                <tr>
                    <td>Biaya Asuransi per Bulan</td>
                    <td>:</td>
                    <td>Rp {{ number_format($application->insurance_monthly, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($totalPayable)
                <tr>
                    <td>Total Kewajiban</td>
                    <td>:</td>
                    <td><strong>Rp {{ number_format($totalPayable, 0, ',', '.') }}</strong></td>
                </tr>
                @endif
                <tr>
                    <td>Tanggal Pencairan</td>
                    <td>:</td>
                    <td><strong>{{ $tglStr }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Perjanjian ini dibuat untuk jangka waktu <strong>{{ $tenor }} ({{ terbilang($tenor) }}) bulan</strong>,
            terhitung sejak tanggal pencairan fasilitas dan wajib lunas pada tanggal
            <strong>{{ $disbDate->copy()->addMonths($tenor)->format('d') }}
            {{ $bulanId[$disbDate->copy()->addMonths($tenor)->month] }}
            {{ $disbDate->copy()->addMonths($tenor)->year }}</strong>.
        </div>
    </div>

    {{-- ===== PASAL 2 ===== --}}
    <div class="pasal-header">Pasal 2</div>
    <div class="pasal-title">Tujuan Penggunaan</div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Fasilitas pembiayaan yang diberikan Pihak Pertama kepada Pihak Kedua berdasarkan Perjanjian ini hanya dapat digunakan oleh Pihak Kedua semata-mata untuk keperluan
            <strong>{{ $application->notes ?? '___________________________' }}</strong>.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Pihak Kedua tidak diperkenankan mengalihkan penggunaan fasilitas pembiayaan ini untuk tujuan lain tanpa persetujuan tertulis dari Pihak Pertama.
        </div>
    </div>

    {{-- ===== PASAL 3 ===== --}}
    <div class="pasal-header">Pasal 3</div>
    <div class="pasal-title">Pembayaran Angsuran</div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Pihak Kedua wajib membayar angsuran sebesar <strong>Rp {{ number_format($angsuran, 0, ',', '.') }}</strong>
            per bulan, paling lambat setiap tanggal <strong>{{ $disbDate->day }}</strong> setiap bulannya.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Keterlambatan pembayaran angsuran akan dikenakan denda sesuai ketentuan yang berlaku di Koperasi.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">3.</div>
        <div class="pasal-item-text">
            Pihak Kedua dapat melunasi kewajibannya sebelum jatuh tempo dengan pemberitahuan terlebih dahulu kepada Pihak Pertama.
        </div>
    </div>

    {{-- ===== PASAL 4 ===== --}}
    @if(!empty($fd['lokasi_agunan'] ?? $fd['jenis_kendaraan'] ?? null))
    <div class="pasal-header">Pasal 4</div>
    <div class="pasal-title">Jaminan / Agunan</div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Sebagai jaminan atas fasilitas pembiayaan ini, Pihak Kedua menyerahkan agunan berupa:
            <table class="detail-table">
                @if(!empty($fd['lokasi_agunan'] ?? null))
                <tr>
                    <td>Jenis Jaminan</td><td>:</td><td>Tanah dan/atau Bangunan</td>
                </tr>
                <tr>
                    <td>Lokasi</td><td>:</td><td>{{ $fd['lokasi_agunan'] }}</td>
                </tr>
                <tr>
                    <td>Status</td><td>:</td><td>{{ $fd['status_agunan'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Atas Nama</td><td>:</td><td>{{ $fd['atas_nama_agunan'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nilai NJOP</td><td>:</td>
                    <td>Rp {{ isset($fd['nilai_agunan']) ? number_format($fd['nilai_agunan'], 0, ',', '.') : '-' }}</td>
                </tr>
                @endif
                @if(!empty($fd['jenis_kendaraan'] ?? null))
                <tr>
                    <td>Jenis Kendaraan</td><td>:</td>
                    <td>{{ $fd['jenis_kendaraan'] }} Tahun {{ $fd['tahun_kendaraan'] ?? '' }}, Warna {{ $fd['warna_kendaraan'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>No. BPKB</td><td>:</td><td>{{ $fd['no_bpkb'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Atas Nama</td><td>:</td><td>{{ $fd['atas_nama_bpkb'] ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif

    {{-- ===== PASAL 5 ===== --}}
    <div class="pasal-header">Pasal {{ !empty($fd['lokasi_agunan'] ?? $fd['jenis_kendaraan'] ?? null) ? 5 : 4 }}</div>
    <div class="pasal-title">Ketentuan Lain-Lain</div>

    <div class="pasal-item clearfix">
        <div class="pasal-item-num">1.</div>
        <div class="pasal-item-text">
            Hal-hal yang belum diatur dalam Perjanjian ini akan diselesaikan secara musyawarah mufakat antara Para Pihak.
        </div>
    </div>
    <div class="pasal-item clearfix">
        <div class="pasal-item-num">2.</div>
        <div class="pasal-item-text">
            Perjanjian ini dibuat dalam rangkap 2 (dua), masing-masing bermaterai cukup dan mempunyai kekuatan hukum yang sama, satu untuk Pihak Pertama dan satu untuk Pihak Kedua.
        </div>
    </div>

    {{-- ===== TANDA TANGAN ===== --}}
    <div class="signature-section">
        <p style="text-align:center; margin-bottom: 8pt;">
            Demikian Perjanjian ini dibuat dan ditandatangani pada hari dan tanggal tersebut di atas.
        </p>
        <table class="signature-grid">
            <tr>
                <td>
                    <strong>Pihak Pertama</strong><br>
                    Koperasi Simpan Pinjam
                    <div class="signature-box">
                        ( ___________________________ )<br>
                        <small>Pengurus Koperasi</small>
                    </div>
                </td>
                <td>
                    <strong>Pihak Kedua</strong><br>
                    Pemohon
                    <div class="signature-box">
                        ( {{ $fd['nama_lengkap'] ?? $member?->name ?? '___________________________' }} )<br>
                        <small>No. KTP: {{ $fd['no_ktp'] ?? '-' }}</small>
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
// Helper: angka ke terbilang (Bahasa Indonesia, max ~999 juta)
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
