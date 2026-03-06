<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengeluaran Kas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 1.5cm 1.8cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.4;
        }

        /* ── Top bar ── */
        .top-bar { display: table; width: 100%; margin-bottom: 4pt; }
        .top-bar .left  { display: table-cell; text-align: left;  font-size: 7.5pt; color: #555; }
        .top-bar .right { display: table-cell; text-align: right; font-size: 7.5pt; color: #555; }

        .company   { font-size: 11pt; font-weight: bold; text-align: center; }
        .module-tag{ font-size: 7.5pt; text-align: center; color: #555; margin-top: 1pt; }

        .divider-thick { border-top: 2pt solid #000; margin: 3pt 0; }
        .divider-thin  { border-top: 0.5pt solid #888; margin: 3pt 0; }

        h1.title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 5pt 0 2pt 0;
        }

        /* ── Meta block ── */
        .meta { display: table; width: 100%; margin: 5pt 0; }
        .meta-left  { display: table-cell; width: 55%; vertical-align: top; }
        .meta-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

        table.meta-tbl { border-collapse: collapse; }
        table.meta-tbl td { font-size: 8pt; padding: 1pt 4pt; vertical-align: top; }
        .ml { width: 95pt; color: #333; }
        .mc { width: 8pt; }

        .status-box {
            display: inline-block;
            padding: 1pt 6pt;
            background-color: #f3f4f6;
            border: 0.5pt solid #9ca3af;
            font-size: 7.5pt;
        }

        /* ── Main table ── */
        table.lines {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8pt;
        }
        table.lines th {
            background-color: #1f2937;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            padding: 4pt 4pt;
            border: 0.5pt solid #374151;
        }
        table.lines td {
            font-size: 8pt;
            padding: 3.5pt 4pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: top;
        }
        table.lines tr.total-row td {
            font-weight: bold;
            background-color: #f3f4f6 !important;
            border-top: 1pt solid #374151;
        }
        table.lines tbody tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }

        /* ── Signature strip ── */
        .sig-strip { display: table; width: 100%; margin-top: 20pt; }
        .sig-col   { display: table-cell; width: 33.33%; text-align: center; font-size: 8pt; padding: 0 6pt; }
        .sig-line  { border-top: 0.5pt solid #000; margin-top: 30pt; padding-top: 2pt; }

        .footer { margin-top: 10pt; font-size: 7.5pt; color: #666; text-align: center; }
    </style>
</head>
<body>
    @php
        $total    = $transactions->sum('amount');
        $dateFrom = $dateFrom ?? null;
        $dateTo   = $dateTo   ?? null;
        $periodLabel = ($dateFrom && $dateTo)
            ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') . ' s/d ' . \Carbon\Carbon::parse($dateTo)->format('d M Y')
            : (($dateFrom ? 'Dari ' . \Carbon\Carbon::parse($dateFrom)->format('d M Y') : '') ?: 'Semua Periode');
    @endphp

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="left">{{ now()->format('d/m/y H:i') }}</div>
        <div class="right">KSP-SMM &mdash; Module : Payment Voucher (PV)</div>
    </div>

    <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
    <div class="module-tag">Finance - Module : Validated Payment Voucher (PV)</div>

    <div class="divider-thick"></div>
    <h1 class="title">Daftar Pengeluaran Kas (PV)</h1>
    <div class="divider-thick"></div>

    {{-- Meta block --}}
    <div class="meta">
        <div class="meta-left">
            <table class="meta-tbl">
                <tr>
                    <td class="ml">Periode</td>
                    <td class="mc">:</td>
                    <td><strong>{{ $periodLabel }}</strong></td>
                </tr>
                @if($filterAccount)
                <tr>
                    <td class="ml">Akun Kas</td>
                    <td class="mc">:</td>
                    <td>{{ $filterAccount }}</td>
                </tr>
                @endif
                @if($filterCategory)
                <tr>
                    <td class="ml">Kategori</td>
                    <td class="mc">:</td>
                    <td>{{ $filterCategory }}</td>
                </tr>
                @endif
                <tr>
                    <td class="ml">Trans.Dt</td>
                    <td class="mc">:</td>
                    <td>{{ $periodLabel }}</td>
                </tr>
                <tr>
                    <td class="ml">Entry.Dt</td>
                    <td class="mc">:</td>
                    <td>{{ now()->format('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="meta-right">
            <table class="meta-tbl" style="float:right; text-align:right;">
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Total Data :</strong> {{ $transactions->count() }} transaksi
                    </td>
                </tr>
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Status :</strong> <span class="status-box">*Printed</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Amount :</strong> IDR {{ number_format($total, 2, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="divider-thin"></div>

    {{-- Transaction table --}}
    <table class="lines">
        <thead>
            <tr>
                <th style="width:18pt">No</th>
                <th style="width:60pt">REF#</th>
                <th style="width:55pt">Kode Sistem</th>
                <th style="width:50pt">Tanggal</th>
                <th>Kategori / Uraian</th>
                <th style="width:80pt">Dari Kas</th>
                <th style="width:80pt" class="text-right">Jumlah (IDR)</th>
                <th style="width:55pt">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $tx)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="font-mono" style="font-size:7.5pt">{{ $tx->reference_code ?: '-' }}</td>
                <td class="font-mono" style="font-size:7pt">{{ $tx->code }}</td>
                <td class="text-center font-mono">{{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}</td>
                <td>
                    {{ $tx->category ?? $tx->cashCategory?->name ?? '-' }}
                    @if($tx->description)
                        <br><span style="font-size:7pt; color:#555;">{{ $tx->description }}</span>
                    @endif
                </td>
                <td>{{ $tx->fromCashAccount?->name ?? '-' }}</td>
                <td class="text-right font-mono">{{ number_format($tx->amount, 2, ',', '.') }}</td>
                <td style="font-size:7pt">{{ $tx->creator?->name ?? $tx->creator?->email ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding:10pt; color:#9ca3af;">
                    Tidak ada data pengeluaran pada filter ini.
                </td>
            </tr>
            @endforelse

            @if($transactions->count() > 0)
            <tr class="total-row">
                <td colspan="6" class="text-right" style="padding-right:8pt;">
                    Total ({{ $transactions->count() }} transaksi) :
                </td>
                <td class="text-right font-mono">{{ number_format($total, 2, ',', '.') }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="divider-thin" style="margin-top:10pt;"></div>

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
