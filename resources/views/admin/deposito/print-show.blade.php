<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bilyet Deposito {{ $deposito->reference_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 1.8cm 2cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; line-height: 1.5; }
        .company { font-size: 13pt; font-weight: bold; text-align: center; }
        .module-tag { font-size: 8pt; text-align: center; color: #555; margin-top: 2pt; }
        .divider-thick { border-top: 2pt solid #000; margin: 4pt 0; }
        .divider-thin { border-top: 0.5pt solid #888; margin: 3pt 0; }
        h1.title { text-align: center; font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 10pt 0 6pt 0; }
        .ref-box {
            text-align: center; margin: 6pt 0 14pt 0;
            font-family: 'Courier New', monospace; font-size: 11pt; font-weight: bold;
        }
        table.info { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        table.info td { padding: 4pt 6pt; vertical-align: top; font-size: 9.5pt; }
        table.info td.label { width: 35%; color: #333; }
        table.info td.sep { width: 3%; }
        table.info td.value { font-weight: bold; }
        .section-title {
            font-size: 10.5pt; font-weight: bold; margin: 12pt 0 2pt 0;
            padding-bottom: 2pt; border-bottom: 0.5pt solid #888;
        }
        .amount-grid { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        .amount-grid td { padding: 6pt 8pt; border: 0.5pt solid #aaa; width: 50%; font-size: 9.5pt; }
        .amount-grid .lbl { font-size: 8pt; color: #555; text-transform: uppercase; }
        .amount-grid .val { font-size: 11pt; font-weight: bold; margin-top: 2pt; }
        .signature { margin-top: 36pt; width: 100%; }
        .signature td { text-align: center; font-size: 9pt; width: 50%; vertical-align: top; }
        .signature .name-line { margin-top: 50pt; border-top: 0.5pt solid #000; padding-top: 3pt; display: inline-block; min-width: 160pt; }
        .footer { margin-top: 20pt; font-size: 7.5pt; color: #666; text-align: center; border-top: 0.5pt solid #ccc; padding-top: 4pt; }
    </style>
</head>
<body>
    <div class="company">KSP — Koperasi Simpan Pinjam</div>
    <div class="module-tag">Bilyet Kontrak Deposito</div>
    <div class="divider-thick"></div>

    <h1 class="title">BILYET DEPOSITO</h1>
    <div class="ref-box">No. Ref: {{ $deposito->reference_code }}</div>

    <div class="section-title">Data Pemilik</div>
    <table class="info">
        <tr>
            <td class="label">Nama Anggota</td><td class="sep">:</td><td class="value">{{ $deposito->member->name }}</td>
        </tr>
        <tr>
            <td class="label">No. Anggota</td><td class="sep">:</td><td class="value">{{ $deposito->member->member_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">No. KTP</td><td class="sep">:</td><td class="value">{{ $deposito->member->ktp_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td><td class="sep">:</td><td class="value">{{ $deposito->member->address ?? '—' }}</td>
        </tr>
    </table>

    <div class="section-title">Data Deposito</div>
    <table class="info">
        <tr>
            <td class="label">Produk</td><td class="sep">:</td><td class="value">{{ $deposito->savingProduct->name }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Mulai</td><td class="sep">:</td><td class="value">{{ $deposito->start_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Jatuh Tempo</td><td class="sep">:</td><td class="value">{{ $deposito->maturity_date->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Tenor</td><td class="sep">:</td><td class="value">{{ $deposito->term_months }} bulan</td>
        </tr>
        <tr>
            <td class="label">Suku Bunga</td><td class="sep">:</td><td class="value">{{ rtrim(rtrim(number_format((float)$deposito->interest_rate_pct, 2, ',', '.'), '0'), ',') }}% per tahun</td>
        </tr>
        <tr>
            <td class="label">Status</td><td class="sep">:</td><td class="value">{{ $deposito->displayStatus() }}</td>
        </tr>
    </table>

    <table class="amount-grid">
        <tr>
            <td>
                <div class="lbl">Pokok Deposito</div>
                <div class="val">Rp {{ number_format($deposito->principal_amount, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="lbl">Bunga saat Jatuh Tempo</div>
                <div class="val">Rp {{ number_format($deposito->interestAmount(), 0, ',', '.') }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="background:#f3f4f6">
                <div class="lbl">Total Payout saat Jatuh Tempo</div>
                <div class="val" style="font-size:13pt">Rp {{ number_format($deposito->payoutAmount(), 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Rekening Pencairan</div>
    <table class="info">
        <tr>
            <td class="label">Nama Pemilik Rek.</td><td class="sep">:</td><td class="value">{{ $deposito->bank_account_name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Rekening</td><td class="sep">:</td><td class="value" style="font-family: 'Courier New', monospace">{{ $deposito->bank_account_no ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Bank</td><td class="sep">:</td><td class="value">{{ $deposito->bank_name ?? '—' }}</td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td>
                Pemilik Deposito<br><br><br><br>
                <span class="name-line">{{ $deposito->member->name }}</span>
            </td>
            <td>
                Petugas KSP<br><br><br><br>
                <span class="name-line">{{ $deposito->creator?->name ?? '—' }}</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB — Ref: {{ $deposito->reference_code }}
    </div>
</body>
</html>
