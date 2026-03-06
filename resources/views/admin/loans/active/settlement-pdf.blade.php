<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perhitungan Pelunasan #{{ $loan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 1.5cm 1.8cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.4;
        }

        /* ── Header ── */
        .top-bar { display: table; width: 100%; margin-bottom: 4pt; }
        .top-bar .left  { display: table-cell; text-align: left;  font-size: 7.5pt; color: #555; }
        .top-bar .right { display: table-cell; text-align: right; font-size: 7.5pt; color: #555; }

        .company    { font-size: 11pt; font-weight: bold; text-align: center; }
        .module-tag { font-size: 7.5pt; text-align: center; color: #555; margin-top: 1pt; }

        .divider-thick { border-top: 2pt solid #000; margin: 3pt 0; }
        .divider-thin  { border-top: 0.5pt solid #888; margin: 3pt 0; }

        h1.title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 6pt 0 3pt 0;
        }
        .subtitle { text-align: center; font-size: 8pt; color: #555; margin-bottom: 6pt; }

        /* ── Info block ── */
        .info-block { display: table; width: 100%; margin: 8pt 0; }
        .info-left  { display: table-cell; width: 55%; vertical-align: top; }
        .info-right { display: table-cell; width: 45%; vertical-align: top; }

        table.info-tbl { border-collapse: collapse; }
        table.info-tbl td { font-size: 8pt; padding: 1.5pt 4pt; vertical-align: top; }
        .il { width: 110pt; color: #444; }
        .ic { width: 8pt; }
        .iv { font-weight: normal; }

        /* ── Breakdown table ── */
        table.breakdown {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10pt;
        }
        table.breakdown th {
            background-color: #1f2937;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            padding: 4pt 6pt;
            text-align: left;
            border: 0.5pt solid #374151;
        }
        table.breakdown th.text-right { text-align: right; }
        table.breakdown td {
            font-size: 8pt;
            padding: 3.5pt 6pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: top;
        }
        table.breakdown tr.section-header td {
            background-color: #f3f4f6;
            font-size: 7.5pt;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3pt 6pt;
            border-top: 1pt solid #9ca3af;
        }
        table.breakdown tr.detail-row td {
            font-size: 7.5pt;
            color: #555;
            padding: 2pt 6pt 2pt 18pt;
        }
        table.breakdown tr.subtotal-row td {
            font-weight: bold;
            border-top: 0.5pt solid #9ca3af;
        }
        table.breakdown tr.total-row td {
            font-weight: bold;
            font-size: 10pt;
            background-color: #1f2937;
            color: #fff;
            border: 1pt solid #111827;
            padding: 5pt 6pt;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }
        .text-red    { color: #991b1b; }
        .text-orange { color: #92400e; }

        /* ── Signature strip ── */
        .sig-strip { display: table; width: 100%; margin-top: 30pt; }
        .sig-col   { display: table-cell; width: 33.33%; text-align: center; font-size: 8pt; padding: 0 6pt; }
        .sig-line  { border-top: 0.5pt solid #000; margin-top: 35pt; padding-top: 2pt; }

        .footer { margin-top: 12pt; font-size: 7.5pt; color: #666; text-align: center; }

        .notice {
            margin-top: 10pt;
            font-size: 7.5pt;
            color: #555;
            border: 0.5pt solid #d1d5db;
            padding: 4pt 8pt;
            background-color: #fafafa;
        }
    </style>
</head>
<body>

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="left">{{ now()->format('d/m/y H:i') }}</div>
        <div class="right">KSP-SMM &mdash; Perhitungan Pelunasan</div>
    </div>

    <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
    <div class="module-tag">Finance — Module : Loan Settlement Calculation</div>

    <div class="divider-thick"></div>
    <h1 class="title">Perhitungan Pelunasan Pinjaman</h1>
    <div class="subtitle">per Tanggal {{ $calc['settlement_date']->format('d M Y') }}</div>
    <div class="divider-thick"></div>

    {{-- Info block --}}
    <div class="info-block">
        <div class="info-left">
            <table class="info-tbl">
                <tr>
                    <td class="il">Nomor Pinjaman</td>
                    <td class="ic">:</td>
                    <td class="iv"><strong>{{ $loan->id }}</strong></td>
                </tr>
                <tr>
                    <td class="il">Nama Anggota</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ $loan->member?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="il">No. Anggota</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ $loan->member?->member_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="il">Produk Pinjaman</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ $loan->loanProduct?->name ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="info-right">
            <table class="info-tbl">
                <tr>
                    <td class="il">Tanggal Cair</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ \Carbon\Carbon::parse($loan->disbursed_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="il">Tenor</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ $loan->tenor_months }} bulan</td>
                </tr>
                <tr>
                    <td class="il">Pokok Pinjaman</td>
                    <td class="ic">:</td>
                    <td class="iv font-mono">IDR {{ number_format($loan->principal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="il">Angsuran Terbayar</td>
                    <td class="ic">:</td>
                    <td class="iv">{{ $calc['paid_count'] }} dari {{ $loan->tenor_months }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="divider-thin"></div>

    {{-- Breakdown table --}}
    <table class="breakdown">
        <thead>
            <tr>
                <th>Uraian</th>
                <th class="text-right" style="width:130pt;">Jumlah (IDR)</th>
            </tr>
        </thead>
        <tbody>

            {{-- Outstanding --}}
            <tr class="section-header">
                <td colspan="2">Sisa Kewajiban Pokok</td>
            </tr>
            <tr>
                <td>Sisa Tagihan (Outstanding Principal + Bunga + Admin + Asuransi)</td>
                <td class="text-right font-mono">{{ number_format($calc['outstanding'], 2, ',', '.') }}</td>
            </tr>

            @if($calc['denda_keterlambatan'] > 0)
            {{-- Denda keterlambatan --}}
            <tr class="section-header">
                <td colspan="2">
                    Denda Keterlambatan
                    ({{ number_format($calc['penalty_daily_rate_bp'] / 100, 2) }}%/hari)
                </td>
            </tr>
            @foreach($calc['denda_details'] as $dd)
            <tr class="detail-row">
                <td>
                    Angsuran #{{ $dd['installment_no'] }} — jatuh tempo {{ $dd['due_date']->format('d M Y') }}
                    — {{ $dd['days_late'] }} hari terlambat
                </td>
                <td class="text-right font-mono text-red">{{ number_format($dd['penalty'], 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="subtotal-row">
                <td>Total Denda Keterlambatan</td>
                <td class="text-right font-mono text-red">{{ number_format($calc['denda_keterlambatan'], 2, ',', '.') }}</td>
            </tr>
            @endif

            @if($calc['is_early_repayment'])
            {{-- Biaya pelunasan dipercepat --}}
            <tr class="section-header">
                <td colspan="2">
                    Biaya Pelunasan Dipercepat
                    (sisa {{ $calc['remaining_periods'] }} angsuran)
                </td>
            </tr>
            <tr>
                <td>
                    Biaya Pelunasan Dipercepat
                    <span style="font-size:7.5pt; color:#666;">
                        ({{ number_format($calc['early_repayment_rate_bp'] / 100, 2) }}% × sisa pokok)
                    </span>
                </td>
                <td class="text-right font-mono text-orange">{{ number_format($calc['biaya_pelunasan_dipercepat'], 2, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Grand total --}}
            <tr class="total-row">
                <td>TOTAL JUMLAH PELUNASAN</td>
                <td class="text-right font-mono">{{ number_format($calc['total_pelunasan'], 2, ',', '.') }}</td>
            </tr>

        </tbody>
    </table>

    {{-- Notice --}}
    <div class="notice">
        Catatan: Perhitungan ini bersifat estimasi per tanggal {{ $calc['settlement_date']->format('d M Y') }}.
        Jumlah pelunasan aktual dapat berbeda apabila tanggal realisasi berbeda.
        Angka Outstanding mencakup: pokok, bunga, biaya admin, dan biaya asuransi yang belum dibayarkan.
    </div>

    {{-- Signature strip --}}
    <div class="sig-strip">
        <div class="sig-col">
            <div>Dibuat oleh,</div>
            <div class="sig-line">( ___________________ )</div>
        </div>
        <div class="sig-col">
            <div>Diperiksa oleh,</div>
            <div class="sig-line">( ___________________ )</div>
        </div>
        <div class="sig-col">
            <div>Disetujui oleh,</div>
            <div class="sig-line">( ___________________ )</div>
        </div>
    </div>

    <div class="footer">
        Dicetak: {{ now()->format('d M Y H:i') }} &mdash; KSP-SMM
    </div>

</body>
</html>
