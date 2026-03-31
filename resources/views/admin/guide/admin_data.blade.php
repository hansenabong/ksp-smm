@extends('admin.guide.layout')
@section('title', 'Panduan Admin Data')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — ADMIN DATA</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#dbeafe;color:#1d4ed8;">ADMIN DATA</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#pengajuan">Pengajuan Pinjaman</a></li>
        <li><a href="#approved">Pinjaman Approved (Lihat)</a></li>
        <li><a href="#anggota">Master Anggota</a></li>
        <li><a href="#agunan">Agunan / Jaminan</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Admin Data bertanggung jawab untuk mengelola pengajuan pinjaman, data anggota, dan data agunan/jaminan.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP, masukkan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Admin Data</strong> dengan sub-menu: Pengajuan Pinjaman, Pinjaman Approved, Master Anggota.
</div>
<div class="step-box">
    <span class="step-num">3</span> Jika ada pengajuan pending, akan muncul <strong>notifikasi kuning</strong> di bagian atas.
</div>

<h2 id="pengajuan">2. Pengajuan Pinjaman</h2>

<h3>2.1 Melihat Daftar Pengajuan</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Data → Pengajuan Pinjaman</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar semua pengajuan beserta statusnya: DRAFT, PENDING, APPROVED, REJECTED.
</div>

<h3>2.2 Membuat Pengajuan Baru</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik tombol <strong>"Tambah Pengajuan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Pilih <strong>Anggota</strong> yang mengajukan pinjaman.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Jenis Pinjaman</strong> (Reguler/Mikro/dll).
</div>
<div class="step-box">
    <span class="step-num">4</span> Isi detail: <strong>Jumlah pinjaman, Tenor</strong> (bulan), dan <strong>Keterangan</strong>.
</div>
<div class="step-box">
    <span class="step-num">5</span> Upload <strong>dokumen pendukung</strong> jika diperlukan (KTP, slip gaji, dll).
</div>
<div class="step-box">
    <span class="step-num">6</span> Klik <strong>"Simpan"</strong>. Pengajuan akan berstatus PENDING menunggu persetujuan Super Admin.
</div>

<h3>2.3 Mengedit Pengajuan (Draft/Pending)</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik pada pengajuan yang ingin diedit.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik tombol <strong>"Edit"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Ubah data yang diperlukan, lalu <strong>"Simpan"</strong>.
</div>

<div class="warning-box">
    <strong>Catatan:</strong> Pengajuan yang sudah APPROVED atau REJECTED tidak bisa diedit. Hanya pengajuan dengan status DRAFT atau PENDING yang bisa diubah.
</div>

<h3>2.4 Melihat Detail & Dokumen</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik pada pengajuan untuk melihat detail lengkap.
</div>
<div class="step-box">
    <span class="step-num">2</span> Di halaman detail, Anda bisa:
    <ul>
        <li>Lihat data lengkap anggota dan pinjaman</li>
        <li>Download file yang sudah diupload</li>
        <li>Lihat <strong>Penawaran Pembiayaan</strong> (PDF)</li>
        <li>Lihat dokumen <strong>PK Sertipikat (A)</strong> dan <strong>PK Kendaraan (B)</strong></li>
    </ul>
</div>

<h3>2.5 Upload / Kelola File</h3>
<div class="step-box">
    <span class="step-num">1</span> Di halaman detail pengajuan, scroll ke bagian <strong>Dokumen</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Upload File"</strong> untuk menambah dokumen baru.
</div>
<div class="step-box">
    <span class="step-num">3</span> File bisa di-download atau dihapus sesuai kebutuhan.
</div>

<h2 id="approved">3. Pinjaman Approved (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Data → Pinjaman Approved</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar pinjaman yang sudah disetujui. Anda bisa melihat detail tetapi <strong>tidak bisa mengubah</strong> data pinjaman.
</div>
<div class="step-box">
    <span class="step-num">3</span> Dari detail pinjaman, Anda bisa melihat:
    <ul>
        <li><strong>Jadwal Angsuran</strong></li>
        <li><strong>Sub Ledger</strong> (rincian pembayaran)</li>
        <li>Status pelunasan</li>
    </ul>
</div>

<h2 id="anggota">4. Master Anggota</h2>

<h3>4.1 Melihat Daftar Anggota</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Data → Master Anggota</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar semua anggota koperasi beserta status (aktif/nonaktif).
</div>

<h3>4.2 Menambah Anggota Baru</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <strong>"Tambah Anggota"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Isi data lengkap: Nomor Anggota, Nama, NIK/KTP, Alamat, No. HP, dll.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Simpan"</strong>.
</div>

<h3>4.3 Mengedit Anggota</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik ikon <strong>Edit</strong> pada baris anggota.
</div>
<div class="step-box">
    <span class="step-num">2</span> Ubah data, lalu <strong>"Simpan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Untuk menonaktifkan anggota, toggle status <strong>Aktif/Nonaktif</strong>.
</div>

<h2 id="agunan">5. Agunan / Jaminan</h2>
<div class="step-box">
    <span class="step-num">1</span> Agunan dikelola dari halaman detail <strong>Pinjaman Approved</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik pinjaman → scroll ke bagian <strong>Agunan</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Tambah Agunan"</strong> untuk mendaftarkan jaminan baru.
</div>
<div class="step-box">
    <span class="step-num">4</span> Upload dokumen agunan (foto sertifikat, BPKB, dll).
</div>

<div class="info-box">
    <strong>Catatan:</strong> Melepas (release) dan menghapus agunan hanya bisa dilakukan oleh Super Admin.
</div>

<h2 id="password">6. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
