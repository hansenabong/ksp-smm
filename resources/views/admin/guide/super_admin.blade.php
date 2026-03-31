@extends('admin.guide.layout')
@section('title', 'Panduan Super Admin')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — SUPER ADMIN</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#ffe4e6;color:#be123c;">SUPER ADMIN</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#users">Manajemen User</a></li>
        <li><a href="#approval">Persetujuan Pinjaman</a></li>
        <li><a href="#void">VOID Transaksi</a></li>
        <li><a href="#coa">Bagan Akun (COA)</a></li>
        <li><a href="#shu">SHU (Sisa Hasil Usaha)</a></li>
        <li><a href="#collateral">Agunan</a></li>
        <li><a href="#all-features">Akses Semua Fitur</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Sebagai Super Admin, Anda memiliki akses penuh ke seluruh fitur sistem. Panduan ini fokus pada fitur-fitur yang hanya bisa dilakukan oleh Super Admin.
</div>

<h2 id="login">1. Login & Dashboard</h2>

<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP di browser.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan email dan password yang telah diberikan.
</div>
<div class="step-box">
    <span class="step-num">3</span> Setelah login, Anda akan melihat <strong>Dashboard</strong> dengan semua menu departemen:
    Admin Data, Admin Collection, Treasury, Legal, Finance, dan Sistem.
</div>
<div class="step-box">
    <span class="step-num">4</span> Di bagian atas terdapat <strong>KPI Summary</strong>: Anggota Aktif, Total Saldo Kas, Total Simpanan, dan Piutang Pinjaman.
</div>

<h2 id="users">2. Manajemen User</h2>
<p>Hanya Super Admin yang dapat mengelola akun pengguna sistem.</p>

<h3>Menambah User Baru</h3>
<div class="step-box">
    <span class="step-num">1</span> Dari Dashboard, klik menu <span class="menu-path">Sistem → Manajemen User</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik tombol <strong>"Tambah User"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Isi formulir: Nama, Email, Password, dan pilih <strong>Role</strong> yang sesuai.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<div class="warning-box">
    <strong>Perhatian:</strong> Role yang tersedia untuk dipilih: FINANCE, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, PENGAWAS, dan LEGAL. Role SUPER_ADMIN tidak bisa di-assign melalui form ini.
</div>

<h3>Mengedit / Menghapus User</h3>
<div class="step-box">
    <span class="step-num">1</span> Di halaman Manajemen User, klik ikon <strong>Edit</strong> (pensil) pada baris user yang ingin diubah.
</div>
<div class="step-box">
    <span class="step-num">2</span> Ubah data yang diperlukan, lalu klik <strong>"Simpan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Untuk menghapus, klik ikon <strong>Hapus</strong> (tempat sampah) dan konfirmasi.
</div>

<h2 id="approval">3. Persetujuan Pinjaman</h2>
<p>Hanya Super Admin yang dapat menyetujui atau menolak pengajuan pinjaman.</p>

<div class="step-box">
    <span class="step-num">1</span> Dari Dashboard, klik menu <span class="menu-path">Admin Data → Pengajuan Pinjaman</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Pengajuan dengan status <strong>"PENDING"</strong> memerlukan tindakan Anda.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik pada pengajuan untuk melihat detail lengkap (data anggota, jumlah pinjaman, tenor, jaminan, dll).
</div>
<div class="step-box">
    <span class="step-num">4</span> Untuk <strong>Menyetujui</strong>: klik tombol <strong>"Approve"</strong>. Pinjaman akan pindah ke daftar Pinjaman Approved.
</div>
<div class="step-box">
    <span class="step-num">5</span> Untuk <strong>Menolak</strong>: klik tombol <strong>"Reject"</strong>.
</div>

<div class="info-box">
    Super Admin juga dapat mengedit <strong>data personal</strong> (KTP, penghasilan, pekerjaan) pada pengajuan pinjaman, yang tidak bisa dilakukan oleh role lain.
</div>

<h2 id="void">4. VOID Transaksi</h2>
<p>Super Admin dapat membatalkan (void) transaksi yang sudah tercatat.</p>

<h3>VOID Kas</h3>
<div class="step-box">
    <span class="step-num">1</span> Buka transaksi kas yang ingin di-void melalui <span class="menu-path">Finance → Pemasukan/Pengeluaran/Transfer</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik pada transaksi, lalu klik tombol <strong>"Void"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Masukkan alasan void, lalu konfirmasi.
</div>

<h3>VOID Simpanan</h3>
<div class="step-box">
    <span class="step-num">1</span> Buka transaksi simpanan melalui <span class="menu-path">Finance → Setoran/Penarikan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik transaksi, lalu <strong>"Void"</strong>.
</div>

<h3>VOID Angsuran Pinjaman</h3>
<div class="step-box">
    <span class="step-num">1</span> Buka pinjaman approved melalui <span class="menu-path">Admin Data → Pinjaman Approved</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat detail pinjaman, cari pembayaran yang ingin di-void.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Void"</strong> pada pembayaran tersebut.
</div>

<div class="warning-box">
    <strong>Penting:</strong> Void tidak dapat dibatalkan. Semua void tercatat di Audit Log dan dapat dilihat di menu <span class="menu-path">Finance → Audit</span>.
</div>

<h2 id="coa">5. Bagan Akun (COA)</h2>
<p>Super Admin dapat mengelola Chart of Accounts.</p>

<div class="step-box">
    <span class="step-num">1</span> Buka <span class="menu-path">Finance → Akuntansi → Bagan Akun</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Untuk <strong>menambah akun baru</strong>, klik "Tambah Akun" dan isi kode, nama, tipe, dan parent.
</div>
<div class="step-box">
    <span class="step-num">3</span> Untuk <strong>mengedit/menonaktifkan</strong>, klik ikon Edit pada baris akun.
</div>

<h2 id="shu">6. SHU (Sisa Hasil Usaha)</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka <span class="menu-path">Finance → Akuntansi → SHU</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Buat Periode Baru"</strong> untuk memulai perhitungan SHU.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Hitung Ulang"</strong> jika ada perubahan data.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Finalisasi"</strong> jika sudah benar — setelah final tidak bisa diubah.
</div>

<h2 id="collateral">7. Agunan</h2>
<p>Super Admin dapat melepas dan menghapus data agunan.</p>

<div class="step-box">
    <span class="step-num">1</span> Buka <span class="menu-path">Admin Data → Pinjaman Approved → Detail → Agunan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Release"</strong> untuk melepas agunan setelah pinjaman lunas.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Hapus"</strong> jika data agunan perlu dihapus.
</div>

<h2 id="all-features">8. Akses Semua Fitur</h2>
<p>Sebagai Super Admin, Anda juga memiliki akses ke semua fitur yang dimiliki role lain:</p>

<table>
    <tr><th>Departemen</th><th>Fitur</th></tr>
    <tr><td>Finance</td><td>Kas, Simpanan, Laporan, Akuntansi, Audit, Master Data Keuangan</td></tr>
    <tr><td>Admin Data</td><td>Pengajuan Pinjaman, Pinjaman Approved, Master Anggota, Agunan</td></tr>
    <tr><td>Admin Collection</td><td>Master Tagihan, DHIA, Laporan Collection, Surat Penagihan, Bayar Angsuran</td></tr>
    <tr><td>Treasury</td><td>Cetak PK, Kwitansi, Permohonan Dana, Jadwal Angsuran</td></tr>
    <tr><td>Legal</td><td>Dokumen Legal</td></tr>
</table>

<p>Lihat panduan masing-masing role untuk detail penggunaan fitur tersebut.</p>

<h2 id="password">9. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas.
</div>
<div class="step-box">
    <span class="step-num">2</span> Pilih <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Masukkan password lama, password baru, dan konfirmasi password baru.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<div class="warning-box">
    <strong>Keamanan:</strong> Segera ganti password default setelah login pertama kali. Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.
</div>
@endsection
