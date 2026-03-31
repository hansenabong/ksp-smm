@extends('admin.guide.layout')
@section('title', 'Panduan Admin Collection')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — ADMIN COLLECTION</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#fef3c7;color:#92400e;">ADMIN COLLECTION</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#tagihan">Master Tagihan Nasabah</a></li>
        <li><a href="#bayar">Bayar Angsuran</a></li>
        <li><a href="#surat">Surat Penagihan</a></li>
        <li><a href="#dhia">Data Master DHIA</a></li>
        <li><a href="#laporan">Laporan Collection</a></li>
        <li><a href="#approved">Pinjaman Approved (Lihat)</a></li>
        <li><a href="#anggota">Master Anggota (Lihat)</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Admin Collection bertanggung jawab atas penagihan angsuran, pencatatan pembayaran, dan monitoring kolektibilitas pinjaman.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP, login dengan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Admin Collection</strong> dengan sub-menu: Master Tagihan Nasabah, Data Master DHIA, Laporan Collection.
</div>

<h2 id="tagihan">2. Master Tagihan Nasabah</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Master Tagihan Nasabah</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar semua tagihan yang harus dikelola, termasuk kolom:
    <ul>
        <li><strong>Nama Anggota</strong></li>
        <li><strong>Sisa Pinjaman</strong> (outstanding)</li>
        <li><strong>Progress</strong> pembayaran</li>
        <li><strong>Status Kolektibilitas</strong></li>
    </ul>
</div>
<div class="step-box">
    <span class="step-num">3</span> Gunakan <strong>filter dan sorting</strong> untuk memfokuskan pencarian (berdasarkan status, periode, dll).
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Export Excel"</strong> untuk mengunduh data tagihan.
</div>

<h2 id="bayar">3. Bayar Angsuran</h2>
<p>Mencatat pembayaran angsuran dari anggota.</p>

<div class="step-box">
    <span class="step-num">1</span> Dari daftar tagihan atau detail pinjaman, klik tombol <strong>"Bayar Angsuran"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Sistem menampilkan:
    <ul>
        <li>Jumlah angsuran yang harus dibayar (pokok + bunga)</li>
        <li>Denda keterlambatan (jika ada)</li>
        <li>Total yang harus dibayar</li>
    </ul>
</div>
<div class="step-box">
    <span class="step-num">3</span> Isi <strong>jumlah yang dibayar</strong> dan pilih <strong>rekening kas</strong> penerima.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>. Kwitansi pembayaran bisa langsung dicetak.
</div>

<div class="warning-box">
    <strong>Penting:</strong> Jika pembayaran salah, hanya Super Admin yang bisa melakukan VOID. Segera laporkan jika ada kesalahan input.
</div>

<h2 id="surat">4. Surat Penagihan</h2>
<div class="step-box">
    <span class="step-num">1</span> Dari detail pinjaman/tagihan, klik <strong>"Buat Surat Penagihan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Pilih jenis surat penagihan (SP1, SP2, SP3).
</div>
<div class="step-box">
    <span class="step-num">3</span> Sistem menghasilkan <strong>surat PDF</strong> yang bisa dicetak.
</div>
<div class="step-box">
    <span class="step-num">4</span> Surat tercatat di sistem untuk tracking proses penagihan.
</div>

<h2 id="dhia">5. Data Master DHIA</h2>
<p>Database Harian Informasi Agunan — berisi data agunan/jaminan semua pinjaman.</p>

<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Data Master DHIA</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat daftar agunan beserta status dan detail. Export tersedia.
</div>

<h2 id="laporan">6. Laporan Collection</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Laporan Collection</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil ringkasan:
    <ul>
        <li><strong>Kolektibilitas</strong>: Lancar, Kurang Lancar, Diragukan, Macet</li>
        <li>Statistik performa penagihan</li>
    </ul>
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Export Excel"</strong> atau <strong>"Cetak"</strong>.
</div>

<h2 id="approved">7. Pinjaman Approved (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Anda bisa melihat daftar pinjaman yang sudah approved dari <span class="menu-path">Pinjaman Approved</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat detail pinjaman, jadwal angsuran, sub ledger, dan status pelunasan.
</div>

<div class="info-box">
    Akses ini bersifat <strong>read-only</strong>. Anda tidak bisa mengubah data pinjaman.
</div>

<h2 id="anggota">8. Master Anggota (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Anda bisa melihat data anggota dari <span class="menu-path">Master Anggota</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Akses bersifat <strong>read-only</strong> — tidak bisa menambah atau mengubah data.
</div>

<h2 id="password">9. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
