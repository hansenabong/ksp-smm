@extends('admin.guide.layout')
@section('title', 'Panduan Finance')

@section('content')
<div class="header">
    <h1>Panduan Pengguna — FINANCE</h1>
    <div class="subtitle">Sistem Koperasi Simpan Pinjam (KSP)</div>
    <span class="role-badge" style="background:#d1fae5;color:#047857;">FINANCE</span>
</div>

<div class="toc">
    <h3>Daftar Isi</h3>
    <ol>
        <li><a href="#login">Login & Dashboard</a></li>
        <li><a href="#kas">Manajemen Kas</a></li>
        <li><a href="#simpanan">Manajemen Simpanan</a></li>
        <li><a href="#laporan">Laporan</a></li>
        <li><a href="#master">Master Data Keuangan</a></li>
        <li><a href="#akuntansi">Akuntansi</a></li>
        <li><a href="#audit">Audit</a></li>
        <li><a href="#anggota">Master Anggota</a></li>
        <li><a href="#password">Ganti Password</a></li>
    </ol>
</div>

<div class="info-box">
    <strong>Role Anda:</strong> Finance bertanggung jawab atas seluruh transaksi keuangan koperasi: kas, simpanan, laporan, akuntansi, dan audit.
</div>

<h2 id="login">1. Login & Dashboard</h2>
<div class="step-box">
    <span class="step-num">1</span> Buka aplikasi KSP di browser, masukkan email dan password Anda.
</div>
<div class="step-box">
    <span class="step-num">2</span> Dashboard menampilkan menu <strong>Finance</strong> dengan sub-menu: Kas, Simpanan, Laporan, Master Data, Audit, dan Akuntansi.
</div>
<div class="step-box">
    <span class="step-num">3</span> Di bagian atas terdapat <strong>KPI</strong>: Anggota Aktif, Total Saldo Kas, Total Simpanan, Piutang Pinjaman.
</div>

<h2 id="kas">2. Manajemen Kas</h2>

<h3>2.1 Melihat Saldo Kas</h3>
<div class="step-box">
    <span class="step-num">1</span> Dari Dashboard, klik <span class="menu-path">Finance → Saldo Kas</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar semua rekening kas/bank beserta saldonya. Klik <strong>"Cetak"</strong> untuk print saldo.
</div>

<h3>2.2 Pemasukan (Kas Masuk)</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Pemasukan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Tambah Pemasukan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Rekening Kas</strong> tujuan, <strong>Kategori</strong> (Modal/Saldo Awal, Pendapatan Jasa, Denda, dll), isi <strong>Jumlah</strong> dan <strong>Keterangan</strong>.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>. Transaksi tercatat dan jurnal akuntansi otomatis dibuat.
</div>
<div class="step-box">
    <span class="step-num">5</span> Klik transaksi untuk melihat detail dan <strong>cetak voucher</strong>.
</div>

<h3>2.3 Pengeluaran (Kas Keluar)</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Pengeluaran</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Tambah Pengeluaran"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Rekening Kas</strong> sumber, <strong>Kategori</strong> (Operasional, Biaya Admin/Bank, Gaji, dll), isi <strong>Jumlah</strong> dan <strong>Keterangan</strong>.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<h3>2.4 Transfer Antar Kas</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Transfer</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Transfer Baru"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Rekening Asal</strong> dan <strong>Rekening Tujuan</strong>, pilih <strong>Kategori</strong> (Setor Bank, Tarik Tunai, Transfer Antar Kas), isi <strong>Jumlah</strong>.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<div class="warning-box">
    <strong>Perhatian:</strong> Semua transaksi kas menghasilkan jurnal akuntansi otomatis. Jika ada kesalahan, minta Super Admin untuk melakukan VOID.
</div>

<h2 id="simpanan">3. Manajemen Simpanan</h2>

<h3>3.1 Setoran Simpanan</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Setoran</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Tambah Setoran"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Anggota</strong>, pilih <strong>Jenis Simpanan</strong> (Wajib/Sukarela/Deposito), pilih <strong>Rekening Kas</strong> penerima, isi <strong>Jumlah</strong>.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>. Voucher bisa dicetak dari halaman detail.
</div>

<h3>3.2 Penarikan Simpanan</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Penarikan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Klik <strong>"Tambah Penarikan"</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Pilih <strong>Anggota</strong>, pilih <strong>Jenis Simpanan</strong> (hanya yang bisa ditarik), pilih <strong>Rekening Kas</strong> sumber, isi <strong>Jumlah</strong>.
</div>
<div class="step-box">
    <span class="step-num">4</span> Klik <strong>"Simpan"</strong>.
</div>

<div class="info-box">
    <strong>Catatan:</strong> Simpanan Wajib dan Deposito biasanya tidak dapat ditarik — tergantung pengaturan jenis simpanan.
</div>

<h3>3.3 Saldo Simpanan Anggota</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Saldo Anggota</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil daftar anggota dan total saldo simpanannya. Klik anggota untuk melihat rincian per jenis simpanan.
</div>

<h2 id="laporan">4. Laporan</h2>

<h3>4.1 Laporan Kas</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Laporan → Kas</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Filter berdasarkan <strong>periode</strong> dan <strong>rekening kas</strong>.
</div>
<div class="step-box">
    <span class="step-num">3</span> Klik <strong>"Export Excel"</strong> atau <strong>"Cetak"</strong> sesuai kebutuhan.
</div>

<h3>4.2 Laporan Simpanan</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Laporan → Simpanan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Filter dan export sesuai kebutuhan.
</div>

<h3>4.3 Laporan Pinjaman</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Laporan → Pinjaman</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tampil ringkasan status pinjaman. Export ke Excel tersedia.
</div>

<h2 id="master">5. Master Data Keuangan</h2>

<h3>5.1 Kategori Kas</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Master Data → Kategori Kas</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tambah/edit kategori pemasukan, pengeluaran, dan transfer. Setiap kategori terhubung ke akun GL.
</div>

<h3>5.2 Rekening Kas/Bank</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Master Data → Kas</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tambah rekening baru: isi Nama, Tipe (CASH/BANK/CHECK/GIRO), dan saldo awal. Akun ledger otomatis dibuat.
</div>

<h3>5.3 Jenis Simpanan</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Master Data → Jenis Simpanan</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tambah/edit produk simpanan: nama, bisa ditarik atau tidak, dll.
</div>

<h3>5.4 Jenis Pinjaman</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Master Data → Jenis Pinjaman</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Tambah/edit produk pinjaman: metode bunga, suku bunga, biaya admin, tenor maksimal, denda, dll.
</div>

<h2 id="akuntansi">6. Akuntansi</h2>

<table>
    <tr><th>Menu</th><th>Fungsi</th></tr>
    <tr><td>Bagan Akun (COA)</td><td>Lihat struktur chart of accounts (edit oleh Super Admin)</td></tr>
    <tr><td>Laba Rugi</td><td>Laporan pendapatan vs beban per periode. Export Excel.</td></tr>
    <tr><td>Neraca</td><td>Posisi aset, kewajiban, dan ekuitas. Export Excel.</td></tr>
    <tr><td>Trial Balance</td><td>Saldo debit/kredit semua akun. Export Excel.</td></tr>
    <tr><td>Buku Besar</td><td>Rincian transaksi per akun GL. Export Excel.</td></tr>
    <tr><td>Jurnal</td><td>Semua jurnal yang dibuat otomatis oleh transaksi. Export Excel.</td></tr>
    <tr><td>SHU</td><td>Lihat perhitungan Sisa Hasil Usaha (buat/finalisasi oleh Super Admin).</td></tr>
</table>

<div class="info-box">
    Semua laporan akuntansi dapat di-export ke Excel. Gunakan filter periode untuk laporan yang akurat.
</div>

<h2 id="audit">7. Audit</h2>

<h3>7.1 Log Aktivitas</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Audit → Log Aktivitas</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat semua aktivitas user: siapa melakukan apa, kapan. Export ke Excel.
</div>

<h3>7.2 VOID Reports</h3>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Audit → VOID Kas / VOID Simpanan / VOID Angsuran</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat daftar transaksi yang sudah di-void beserta alasannya.
</div>

<h2 id="anggota">8. Master Anggota</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik <span class="menu-path">Finance → Master Data → Anggota</span>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Lihat, tambah, atau edit data anggota koperasi.
</div>
<div class="step-box">
    <span class="step-num">3</span> Toggle status aktif/nonaktif anggota sesuai kebutuhan.
</div>

<h2 id="password">9. Ganti Password</h2>
<div class="step-box">
    <span class="step-num">1</span> Klik nama Anda di pojok kanan atas → <strong>"Ganti Password"</strong>.
</div>
<div class="step-box">
    <span class="step-num">2</span> Masukkan password lama dan password baru. Klik <strong>"Simpan"</strong>.
</div>
@endsection
