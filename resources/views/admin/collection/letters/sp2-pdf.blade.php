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

<div class="subject">PERINGATAN II (KEDUA)</div>

<div class="body">
    <p>Dengan hormat,</p>

    <p>
        Sehubungan dengan Surat Peringatan I yang telah kami kirimkan sebelumnya terkait Perjanjian Pembiayaan
        No. <strong>{{ $noPk }}</strong>, hingga saat ini kami <strong>belum menerima pembayaran</strong> dari Bapak/Ibu.
    </p>

    <p>
        Dengan ini kami kembali mengingatkan Bapak/Ibu untuk segera menyelesaikan seluruh tunggakan angsuran
        yang masih tertunggak kepada <strong>{{ $appName }}</strong>.
    </p>

    <p>
        Apabila sampai dengan diterimanya surat ini Bapak/Ibu masih belum melakukan pembayaran, maka kami terpaksa
        akan menerbitkan <strong>SURAT PERINGATAN III</strong> dan mengambil langkah-langkah penyelesaian sesuai
        perjanjian yang berlaku, termasuk <strong>proses pelelangan jaminan</strong>.
    </p>

    <p>
        Kami berharap Bapak/Ibu dapat segera menghubungi kantor kami untuk penyelesaian kewajiban ini sebelum
        tindakan lebih lanjut diambil.
    </p>

    <p>Demikian surat peringatan ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu, kami ucapkan terima kasih.</p>
</div>

<div class="sig-area">
    <p>Hormat kami,</p>
    <p style="margin-top:2pt;"><strong>{{ $appName }}</strong></p>
    <br><br><br>
    <p class="sig-name">Frangky Nafi</p>
    <p style="font-size:9pt;">Head Collection</p>
</div>

<div class="footer-note">
    <em>Catatan: Apabila pembayaran telah dilakukan, harap menyerahkan bukti pembayaran kepada kami dan surat ini dinyatakan batal.
    Informasi lebih lanjut, hubungi Divisi Collection {{ $appName }}.</em>
</div>

</div>
</body>
</html>
