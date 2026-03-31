@extends('admin.guide.layout')
@section('title', 'Panduan Treasury')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — TREASURY</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#ede9fe;color:#6d28d9;">TREASURY</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#pk">Cetak Perjanjian Kredit (PK)</a></li>
        <li><a href="#kwitansi">Cetak Kwitansi</a></li>
        <li><a href="#permohonan">Permohonan Dana</a></li>
        <li><a href="#jadwal">Jadwal Angsuran</a></li>
        <li><a href="#pengajuan">Pengajuan Pinjaman (Lihat)</a></li>
        <li><a href="#dhia">Data Master DHIA (Lihat)</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Treasury bertanggung jawab atas pencetakan dokumen resmi: Perjanjian Kredit, Kwitansi Pencairan, Permohonan Dana, dan Jadwal Angsuran.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP, login dengan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Treasury</strong> dengan sub-menu dokumen yang bisa dicetak.
</div>

<h2 id="pk">2. Cetak Perjanjian Kredit (PK)</h2>
<p>Mencetak dokumen perjanjian kredit untuk pinjaman yang sudah disetujui.</p>

<h3>2.1 PK Tipe A — Sertipikat (Jaminan Properti)</h3>
<div class="step-box">
    <span class="step-num">1</span> Buka detail pengajuan pinjaman yang sudah APPROVED.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik tombol <strong>"PK Sertipikat (A)"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Dokumen PDF akan terbuka — berisi perjanjian lengkap dengan data anggota, pinjaman, dan jaminan properti.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Cetak"</strong> atau simpan sebagai PDF.
</div>

<h3>2.2 PK Tipe B — Kendaraan (Jaminan Kendaraan)</h3>
<div class="step-box">
    <span class="step-num">1</span> Sama seperti Tipe A, tetapi klik <strong>"PK Kendaraan (B)"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dokumen disesuaikan untuk jaminan kendaraan (BPKB).
</div>

<h3>2.3 Penawaran Pembiayaan</h3>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pengajuan, klik <strong>"Penawaran Pembiayaan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dokumen berisi simulasi pinjaman yang bisa diberikan kepada calon peminjam.
</div>

<h2 id="kwitansi">3. Cetak Kwitansi</h2>

<h3>3.1 Kwitansi Pencairan</h3>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pinjaman approved, klik <strong>"Kwitansi Pencairan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Isi/edit data kwitansi jika diperlukan (tanggal, nominal, dll).
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Cetak"</strong> untuk mencetak kwitansi pencairan dana.
</div>

<h3>3.2 Kwitansi Plafon</h3>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pinjaman, klik <strong>"Kwitansi Plafon"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Cetak bukti plafon pinjaman yang disetujui.
</div>

<h2 id="permohonan">4. Permohonan Dana</h2>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pinjaman approved, klik <strong>"Permohonan Dana"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dokumen berisi permohonan pencairan dana pinjaman.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Cetak PDF"</strong> untuk mencetak permohonan.
</div>

<h2 id="jadwal">5. Jadwal Angsuran</h2>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pinjaman approved, klik <strong>"Jadwal Angsuran"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil tabel jadwal pembayaran: tanggal jatuh tempo, angsuran pokok, bunga, dan total per bulan.
</div>
<div class="step-box">
    <span class="step-num">3</span> Cetak jadwal untuk diberikan kepada anggota.
</div>

<h2 id="pengajuan">6. Pengajuan Pinjaman (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Anda bisa melihat daftar pengajuan pinjaman beserta detailnya.
</div>
<div class="step-box">
    <span class="step-num">2</span> Akses bersifat <strong>read-only</strong> — tidak bisa membuat atau mengedit pengajuan.
</div>

<h2 id="dhia">7. Data Master DHIA (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Treasury → Data Master DHIA</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat database agunan/jaminan untuk referensi pembuatan dokumen.
</div>

<h2 id="password">8. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
