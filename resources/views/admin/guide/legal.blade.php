@extends('admin.guide.layout')
@section('title', 'Panduan Legal')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — LEGAL</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#e2e8f0;color:#334155;">LEGAL</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#legal-doc">Dokumen Legal</a></li>
        <li><a href="#tagihan">Master Tagihan (Collection)</a></li>
        <li><a href="#surat">Surat Penagihan</a></li>
        <li><a href="#dhia">Data Master DHIA</a></li>
        <li><a href="#laporan">Laporan Collection</a></li>
        <li><a href="#anggota">Master Anggota (Lihat)</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Legal bertanggung jawab mengelola dokumen hukum dan memantau proses penagihan. Anda memiliki akses penuh ke Dokumen Legal dan akses baca ke modul Collection.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP, login dengan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Legal</strong> dengan sub-menu: Dokumen Legal, Master Tagihan, Data Master DHIA, Master Anggota.
</div>

<h2 id="legal-doc">2. Dokumen Legal</h2>
<p>Fitur utama role Legal — mengelola berkas dan dokumen hukum terkait pinjaman.</p>

<h3>2.1 Melihat Daftar Berkas Legal</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Legal → Dokumen Legal</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar semua berkas legal (bundle), termasuk: Judul, Anggota, Pengajuan Pinjaman terkait, dan tanggal dibuat.
</div>

<h3>2.2 Membuat Berkas Legal Baru</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik tombol <strong>"Tambah Berkas"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Isi formulir:
    <ul>
        <li><strong>Judul</strong>: Nama/deskripsi berkas</li>
        <li><strong>Anggota</strong>: Pilih anggota terkait</li>
        <li><strong>Pengajuan Pinjaman</strong>: Hubungkan ke pengajuan (opsional)</li>
        <li><strong>Keterangan</strong>: Detail tambahan</li>
    </ul>
</div>
<div class="step-box">
    <span class="step-num">3</span> Upload <strong>file dokumen</strong> (scan surat, akta notaris, sertifikat, dll).
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<h3>2.3 Menambah File ke Berkas</h3>
<div class="step-box">
    <span class="step-num">1</span> Buka detail berkas yang sudah ada.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Tambah File"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Upload file baru (bisa beberapa file sekaligus).
</div>

<h3>2.4 Menghapus File / Berkas</h3>
<div class="step-box">
    <span class="step-num">1</span> Untuk menghapus <strong>file individual</strong>: klik ikon hapus pada file di halaman detail.
</div>
<div class="step-box">
    <span class="step-num">2</span> Untuk menghapus <strong>seluruh berkas</strong>: klik tombol "Hapus Berkas" dan konfirmasi.
</div>

<div class="warning-box">
    <strong>Perhatian:</strong> Menghapus berkas akan menghapus semua file di dalamnya secara permanen. Pastikan tidak ada file yang masih diperlukan.
</div>

<h2 id="tagihan">3. Master Tagihan (Collection)</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Legal → Master Tagihan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat daftar tagihan nasabah, termasuk status kolektibilitas dan progress pembayaran.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik tagihan untuk melihat detail pinjaman, jadwal angsuran, dan riwayat pembayaran.
</div>

<h2 id="surat">4. Surat Penagihan</h2>
<div class="step-box">
    <span class="step-num">1</span> Dari detail tagihan, Anda bisa membuat <strong>surat penagihan</strong> (SP1, SP2, SP3).
</div>
<div class="step-box">
    <span class="step-num">2</span> Surat dihasilkan sebagai PDF yang bisa dicetak dan dikirimkan.
</div>

<div class="info-box">
    Surat penagihan tercatat di sistem untuk dokumentasi proses hukum.
</div>

<h2 id="dhia">5. Data Master DHIA</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Legal → Data Master DHIA</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat database agunan/jaminan semua pinjaman. Berguna untuk verifikasi status jaminan dalam proses hukum.
</div>

<h2 id="laporan">6. Laporan Collection</h2>
<div class="step-box">
    <span class="step-num">1</span> Akses laporan kolektibilitas dan performa penagihan.
</div>
<div class="step-box">
    <span class="step-num">2</span> Export ke Excel untuk analisis atau dokumentasi.
</div>

<h2 id="anggota">7. Master Anggota (Lihat Saja)</h2>
<div class="step-box">
    <span class="step-num">1</span> Lihat data anggota koperasi — akses <strong>read-only</strong>.
</div>

<div class="warning-box">
    <strong>Catatan:</strong> Beberapa data sensitif mungkin disembunyikan sesuai kebijakan privasi.
</div>

<h2 id="password">8. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
