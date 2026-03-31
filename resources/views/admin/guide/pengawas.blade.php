@extends('admin.guide.layout')
@section('title', 'Panduan Pengawas')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — PENGAWAS</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#cffafe;color:#0e7490;">PENGAWAS</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#pengajuan">Pengajuan Pinjaman</a></li>
        <li><a href="#approved">Pinjaman Approved</a></li>
        <li><a href="#tagihan">Master Tagihan (Collection)</a></li>
        <li><a href="#dhia">Data Master DHIA</a></li>
        <li><a href="#laporan">Laporan Collection</a></li>
        <li><a href="#anggota">Master Anggota</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Pengawas memiliki akses <strong>read-only</strong> (hanya melihat) untuk memantau seluruh proses pinjaman, penagihan, dan data anggota. Anda tidak dapat mengubah data apapun.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP, login dengan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Admin Data</strong> dan <strong>Admin Collection</strong> — keduanya dalam mode baca saja.
</div>

<h2 id="pengajuan">2. Pengajuan Pinjaman</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Data → Pengajuan Pinjaman</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat daftar semua pengajuan dan statusnya (PENDING, APPROVED, REJECTED).
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik pengajuan untuk melihat detail: data anggota, jumlah pinjaman, tenor, dan dokumen pendukung.
</div>

<div class="info-box">
    Sebagai Pengawas, Anda bisa memonitor proses pengajuan tetapi <strong>tidak dapat menyetujui, menolak, atau mengedit</strong> pengajuan.
</div>

<h2 id="approved">3. Pinjaman Approved</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Data → Pinjaman Approved</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat daftar pinjaman aktif, termasuk:
    <ul>
        <li>Jumlah pinjaman dan sisa outstanding</li>
        <li>Jadwal angsuran</li>
        <li>Sub ledger (rincian pembayaran)</li>
        <li>Status pelunasan</li>
    </ul>
</div>

<h2 id="tagihan">4. Master Tagihan (Collection)</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Master Tagihan Nasabah</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Monitor semua tagihan dan status kolektibilitas:
    <ul>
        <li><strong>Lancar</strong>: Pembayaran tepat waktu</li>
        <li><strong>Kurang Lancar</strong>: Terlambat 1-90 hari</li>
        <li><strong>Diragukan</strong>: Terlambat 91-180 hari</li>
        <li><strong>Macet</strong>: Terlambat lebih dari 180 hari</li>
    </ul>
</div>
<div class="step-box">
    <span class="step-num">3</span> Gunakan filter untuk fokus pada kategori tertentu.
</div>

<h2 id="dhia">5. Data Master DHIA</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Data Master DHIA</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat database agunan/jaminan untuk monitoring nilai jaminan.
</div>

<h2 id="laporan">6. Laporan Collection</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Admin Collection → Laporan Collection</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat ringkasan kolektibilitas dan performa penagihan.
</div>
<div class="step-box">
    <span class="step-num">3</span> Export ke Excel atau cetak untuk dokumentasi pengawasan.
</div>

<h2 id="anggota">7. Master Anggota</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Master Anggota</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat data anggota koperasi. Akses <strong>read-only</strong>.
</div>

<div class="warning-box">
    <strong>Catatan:</strong> Beberapa data sensitif (KTP/NIK) mungkin disembunyikan sesuai kebijakan privasi sistem.
</div>

<h2 id="password">8. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
