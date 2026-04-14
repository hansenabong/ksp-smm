<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; line-height: 1.6; }
    .page { padding: 40pt 50pt; }
    .header-org { text-align: center; margin-bottom: 16pt; }
    .header-org .name { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1pt; }
    .header-org .sub  { font-size: 9pt; color: #444; }
    .divider { border-top: 2pt solid #000; border-bottom: 1pt solid #000; margin: 4pt 0 12pt; padding: 1pt 0; }
    .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14pt; }
    .meta-table td { padding: 1.5pt 0; vertical-align: top; font-size: 11pt; }
    .meta-table .col-label { width: 42%; }
    .meta-table .col-sep   { width: 4%; }
    .meta-table .col-val   { width: 54%; }
    .subject { font-weight: bold; text-decoration: underline; text-align: center; font-size: 12pt; margin: 12pt 0; text-transform: uppercase; letter-spacing: 0.5pt; }
    .body p  { margin-bottom: 10pt; text-align: justify; }
    .sig-area { margin-top: 32pt; }
    .sig-name { font-weight: bold; margin-top: 60pt; border-top: 1pt solid #000; padding-top: 2pt; display: inline-block; min-width: 160pt; }
    .footer-note { margin-top: 20pt; font-size: 9pt; color: #555; border-top: 1pt solid #ddd; padding-top: 8pt; }
    .legal-box { border: 1pt solid #999; padding: 8pt 12pt; margin: 10pt 0; background: #fafafa; font-size: 10pt; }
</style>
</head>
<body>
<div class="page">

@php
    $tgl = $letter->letter_date instanceof \Carbon\Carbon ? $letter->letter_date : \Carbon\Carbon::parse($letter->letter_date);
    $bulanId = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $tglStr  = $tgl->day.' '.$bulanId[$tgl->month].' '.$tgl->year;
    $appName = config('app.name', 'KSP Sarana Mitra Mandiri');
@endphp

<div class="header-org">
    <div class="name">{{ $appName }}</div>
    <div class="sub">Perkantoran Dutamas Fatmawati Blok B1 No. 16 Jakarta Selatan</div>
</div>
<div class="divider"></div>

<table class="meta-table">
    <tr>
        <td class="col-label">Jakarta</td>
        <td class="col-sep">,</td>
        <td class="col-val">{{ $tglStr }}</td>
    </tr>
    <tr>
        <td class="col-label">Nomor</td>
        <td class="col-sep">:</td>
        <td class="col-val">{{ $letter->letter_number }}</td>
    </tr>
    <tr>
        <td class="col-label">Kepada Yth.</td>
        <td class="col-sep">:</td>
        <td class="col-val">Bapak/Ibu {{ $member->name }}</td>
    </tr>
    <tr>
        <td class="col-label">Di</td>
        <td class="col-sep">:</td>
        <td class="col-val">{{ $alamatTinggal }}</td>
    </tr>
</table>

@if($alamatJaminan)
<table class="meta-table" style="margin-top:-8pt;">
    <tr>
        <td class="col-label">Alamat Jaminan{{ $statusAgunan ? ' ('.$statusAgunan.')' : '' }}</td>
        <td class="col-sep">:</td>
        <td class="col-val">{{ $alamatJaminan }}</td>
    </tr>
</table>
@endif

<div class="subject">Pemberitahuan Pengalihan Piutang (Cessie)</div>

<div class="body">
    <p>Dengan hormat,</p>

    <p>
        Merujuk pada Perjanjian Pembiayaan No. <strong>{{ $noPk }}</strong>, berdasarkan catatan keuangan kami,
        Bapak/Ibu masih memiliki kewajiban yang belum diselesaikan kepada <strong>{{ $appName }}</strong>.
    </p>

    <p>
        Bersama ini kami beritahukan bahwa berdasarkan Perjanjian Pembiayaan No. <strong>{{ $noPk }}</strong>
        khususnya Pasal 11 angka 1 dan 2, Bapak/Ibu telah dinyatakan <strong>Wanprestasi</strong>
        karena:
    </p>

    <div class="legal-box">
        <strong>Pasal 11 ayat 1:</strong> Bapak/Ibu tidak memenuhi kewajiban pembayaran angsuran, denda, dan/atau
        biaya lainnya pada waktu yang telah ditetapkan (terbukti dengan keterlambatan pembayaran semata).<br><br>
        <strong>Pasal 11 ayat 2:</strong> Objek pembiayaan dijual, disewakan, dipindahtangankan, dijaminkan,
        atau dihipotekkan kepada pihak ketiga tanpa persetujuan tertulis dari <strong>{{ $appName }}</strong>.
    </div>

    <p>
        Sehubungan dengan kondisi tersebut, dengan ini kami memberitahukan bahwa <strong>{{ $appName }}</strong>
        akan mengalihkan <strong>Piutang (Cessie)</strong> atas nama Bapak/Ibu kepada pihak ketiga.
    </p>

    <p>
        Apabila Bapak/Ibu memerlukan informasi lebih lanjut mengenai pengalihan piutang ini, silakan menghubungi
        kami di alamat:
    </p>

    <p style="padding-left:20pt;">
        <strong>{{ $appName }}</strong><br>
        Perkantoran Dutamas Fatmawati Blok B1 No. 16, Jakarta Selatan
    </p>

    <p>Demikian pemberitahuan ini kami sampaikan.</p>
</div>

<div class="sig-area">
    <p>Hormat kami,</p>
    <p style="margin-top:2pt;"><strong>{{ $appName }}</strong></p>
    <br><br><br>
    <p class="sig-name">Frangky Nafi</p>
    <p style="font-size:9pt;">Head Collection</p>
</div>

<div class="footer-note">
    <em>Pemegang Piutang baru akan diinformasikan setelah dilaksanakannya pengalihan piutang.
    Hubungi Divisi Collection {{ $appName }} untuk informasi lebih lanjut.</em>
</div>

</div>
</body>
</html>
