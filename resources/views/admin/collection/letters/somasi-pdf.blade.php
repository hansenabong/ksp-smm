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
    .subject { font-weight: bold; text-decoration: underline; text-align: center; font-size: 14pt; margin: 14pt 0; text-transform: uppercase; letter-spacing: 2pt; }
    .body p  { margin-bottom: 10pt; text-align: justify; }
    .sig-area { margin-top: 32pt; }
    .sig-name { font-weight: bold; margin-top: 60pt; border-top: 1pt solid #000; padding-top: 2pt; display: inline-block; min-width: 160pt; }
    .footer-note { margin-top: 20pt; font-size: 9pt; color: #555; border-top: 1pt solid #ddd; padding-top: 8pt; }
    .highlight { background-color: #fff3cd; padding: 8pt; border-left: 3pt solid #ffc107; margin: 10pt 0; }
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
        <td class="col-val">{{ $member->address ?? 'Tempat' }}</td>
    </tr>
</table>

<div class="subject">S O M A S I</div>

<div class="body">
    <p>Dengan hormat,</p>

    <p>
        Menunjuk pada Perjanjian Kredit No. <strong>{{ $noPk }}</strong> atas nama
        <strong>{{ $member->name }}</strong>, berdasarkan catatan keuangan kami Bapak/Ibu masih
        memiliki kewajiban kepada <strong>{{ $appName }}</strong> yang hingga saat ini belum diselesaikan.
    </p>

    <p>
        Bahwa surat-surat peringatan yang telah kami kirimkan sebelumnya tidak mendapatkan tanggapan dari
        Bapak/Ibu, maka dengan ini kami <strong>memperingatkan untuk terakhir kalinya</strong> kepada
        Bapak/Ibu untuk segera menyelesaikan seluruh kewajiban yang masih tertunggak.
    </p>

    <div class="highlight">
        Apabila dalam batas waktu yang ditentukan Bapak/Ibu tidak melakukan penyelesaian kewajiban tersebut,
        maka kami akan:
        <br><br>
        1. Menuntut <strong>pelunasan secara sekaligus (opeisbaar)</strong> atas seluruh sisa kewajiban;<br>
        2. Melaksanakan proses <strong>lelang jaminan</strong> berdasarkan Sertipikat Hak Tanggungan
           yang kami pegang;<br>
        3. Melakukan kegiatan pemasaran berupa: <strong>appraisal</strong> objek jaminan,
           <strong>pemasangan iklan di media massa</strong>, <strong>spanduk</strong>,
           <strong>selembaran/flyer</strong>, dan kegiatan pemasaran lainnya;<br>
        4. Seluruh biaya yang timbul atas pelaksanaan hal di atas akan <strong>dibebankan kepada Bapak/Ibu</strong>.
    </div>

    <p>
        Apabila sebelum batas waktu yang ditentukan Bapak/Ibu telah melakukan pembayaran dan menghubungi
        kami, maka surat ini dinyatakan batal dengan sendirinya.
    </p>

    <p>Demikian somasi ini kami sampaikan.</p>
</div>

<div class="sig-area">
    <p>Hormat kami,</p>
    <p style="margin-top:2pt;"><strong>{{ $appName }}</strong></p>
    <br><br><br>
    <p class="sig-name">Frangky Nafi</p>
    <p style="font-size:9pt;">Head Collection</p>
</div>

<div class="footer-note">
    <em>Catatan: Apabila pembayaran telah dilakukan, harap segera menghubungi kami dan menyerahkan bukti pembayaran.
    Hubungi Divisi Collection {{ $appName }}.</em>
</div>

</div>
</body>
</html>
