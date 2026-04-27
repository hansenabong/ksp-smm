<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Piutang / SubLedger #{{ $loan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 1.2cm 1.5cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
            line-height: 1.35;
        }

        /* ── Header ── */
        .top-bar { display: table; width: 100%; margin-bottom: 4pt; }
        .top-bar .left  { display: table-cell; text-align: left;  font-size: 7pt; color: #555; }
        .top-bar .right { display: table-cell; text-align: right; font-size: 7pt; color: #555; }

        .company    { font-size: 11pt; font-weight: bold; text-align: center; }
        .module-tag { font-size: 7.5pt; text-align: center; color: #555; margin-top: 1pt; }

        .divider-thick { border-top: 2pt solid #000; margin: 3pt 0; }
        .divider-thin  { border-top: 0.5pt solid #888; margin: 3pt 0; }

        h1.title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 5pt 0 2pt 0;
        }

        /* ── Agreement info block ── */
        .info-block { display: table; width: 100%; margin: 6pt 0; }
        .info-left  { display: table-cell; width: 55%; vertical-align: top; }
        .info-right { display: table-cell; width: 45%; vertical-align: top; }

        table.info-tbl { border-collapse: collapse; }
        table.info-tbl td { font-size: 7.5pt; padding: 1pt 3pt; vertical-align: top; }
        .il { width: 105pt; color: #444; }
        .ic { width: 8pt; }

        /* ── Main ledger table ── */
        table.ledger {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8pt;
        }
        table.ledger th {
            background-color: #1f2937;
            color: #fff;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            padding: 3pt 3pt;
            border: 0.5pt solid #374151;
            white-space: nowrap;
        }
        table.ledger td {
            font-size: 7pt;
            padding: 2.5pt 3pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: middle;
        }
        table.ledger tr.row-disburse td {
            background-color: #eff6ff;
            font-weight: bold;
        }
        table.ledger tr.row-paid td {
            background-color: #f0fdf4;
        }
        table.ledger tr.row-open td {
            background-color: #fff7ed;
        }
        table.ledger tr.row-total td {
            background-color: #1f2937;
            color: #fff;
            font-weight: bold;
            font-size: 7pt;
            border-top: 1pt solid #111827;
        }
        table.ledger tr.row-total td.text-right { text-align: right; }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }

        .badge-l { color: #166534; font-weight: bold; }
        .badge-o { color: #92400e; font-weight: bold; }

        /* ── Signature strip ── */
        .sig-strip { display: table; width: 100%; margin-top: 20pt; }
        .sig-col   { display: table-cell; width: 33.33%; text-align: center; font-size: 7.5pt; padding: 0 4pt; }
        .sig-line  { border-top: 0.5pt solid #000; margin-top: 30pt; padding-top: 2pt; }

        .footer { margin-top: 8pt; font-size: 7pt; color: #666; text-align: center; }
    </style>
</head>
<body>

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="left">{{ now()->format('d/m/y H:i') }}</div>
        <div class="right">KSP-SMM &mdash; Kartu Piutang / SubLedger Detail</div>
    </div>

    <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
    <div class="module-tag">Finance — Module : Loan Sub-Ledger / Kartu Piutang</div>

    <div class="divider-thick"></div>
    <h1 class="title">Sub Ledger</h1>
    <div class="divider-thick"></div>

    {{-- Info block --}}
    <div class="info-block">
        <div class="info-left">
            <table class="info-tbl">
                <tr>
                    <td class="il">Agreement No.</td>
                    <td class="ic">:</td>
                    <td><strong>{{ $loan->id }}</strong></td>
                </tr>
                <tr>
                    <td class="il">Nama Anggota</td>
                    <td class="ic">:</td>
                    <td>{{ $loan->member?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="il">No. Anggota</td>
                    <td class="ic">:</td>
                    <td>{{ $loan->member?->member_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="il">Akte / No. Deed</td>
                    <td class="ic">:</td>
                    <td>{{ $loan->application?->notaris_deed_number ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="info-right">
            <table class="info-tbl">
                <tr>
                    <td class="il">Produk</td>
                    <td class="ic">:</td>
                    <td>{{ $loan->loanProduct?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="il">Int. Method</td>
                    <td class="ic">:</td>
                    <td>Flat Rate</td>
                </tr>
                <tr>
                    <td class="il">Tanggal Cair</td>
                    <td class="ic">:</td>
                    <td>{{ \Carbon\Carbon::parse($loan->disbursed_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="il">Tenor</td>
                    <td class="ic">:</td>
                    <td>{{ $loan->tenor_months }} bulan</td>
                </tr>
                <tr>
                    <td class="il">Pokok Pinjaman</td>
                    <td class="ic">:</td>
                    <td class="font-mono">IDR {{ number_format($loan->principal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="il">Total Kewajiban</td>
                    <td class="ic">:</td>
                    <td class="font-mono">IDR {{ number_format($loan->total_payable, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="divider-thin"></div>

    {{-- Ledger table --}}
    <table class="ledger">
        <thead>
            <tr>
                <th rowspan="2" style="width:18pt">No</th>
                <th rowspan="2" style="width:16pt">Flag</th>
                <th rowspan="2" style="width:50pt">Tgl Jatuh<br>Tempo</th>
                <th rowspan="2" style="width:62pt">No. Dokumen</th>
                <th rowspan="2" style="width:26pt">Term</th>
                <th rowspan="2" style="width:64pt">Jumlah Angsuran</th>
                <th colspan="2" style="width:80pt; text-align:center;">Denda</th>
                <th rowspan="2" style="width:50pt">Tgl Bayar</th>
                <th rowspan="2" style="width:64pt">Pembayaran</th>
                <th rowspan="2" style="width:64pt">Saldo Piutang</th>
            </tr>
            <tr>
                <th style="width:34pt">Hari</th>
                <th style="width:46pt">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    if ($row['is_disburse']) {
                        $trClass = 'row-disburse';
                    } elseif ($row['payment'] > 0) {
                        $trClass = 'row-paid';
                    } else {
                        $trClass = 'row-open';
                    }
                @endphp
                @php
                    // Recalculate both Hari and Jumlah from actual dates × rate
                    // (stored penalty_days was a slot count, stored penalty_amount was full accumulated total on each row)
                    if (!$row['is_disburse'] && isset($row['payment_obj']) && $row['payment_obj']) {
                        $payObj      = $row['payment_obj'];
                        $payDate     = \Carbon\Carbon::parse($payObj->trx_date);
                        $dueDate     = $row['doc_date'];
                        $dendaHari   = $payDate->gt($dueDate) ? (int) $dueDate->diffInDays($payDate) : 0;
                        $dendaRateBp = $loan->loanProduct?->penalty_daily_rate_bp ?? 30;
                        $dendaBase   = (int) $loan->monthly_installment;
                        $dendaAmount = $dendaHari > 0
                            ? (int) round($dendaBase * $dendaHari * $dendaRateBp / 10000)
                            : 0;
                        // Early repayment penalty is separate and stored correctly
                        $dendaAmount += (int)($payObj->early_repayment_penalty ?? 0);
                    } else {
                        $dendaHari   = 0;
                        $dendaAmount = 0;
                    }
                @endphp
                <tr class="{{ $trClass }}">
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td class="text-center {{ $row['flag'] === 'L' ? 'badge-l' : 'badge-o' }}">{{ $row['flag'] }}</td>
                    <td class="text-center font-mono">{{ $row['doc_date']->format('d/m/Y') }}</td>
                    <td class="font-mono" style="font-size:6.5pt;">{{ $row['doc_no'] ?: '-' }}</td>
                    <td class="text-center">{{ $row['payterm'] > 0 ? $row['payterm'] : '-' }}</td>
                    <td class="text-right font-mono">
                        @if($row['is_disburse'])
                            —
                        @else
                            {{ number_format($row['amount'], 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-center font-mono">
                        @if($row['is_disburse'])
                            —
                        @elseif($dendaHari > 0)
                            {{ $dendaHari }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right font-mono" style="color:{{ $dendaAmount > 0 ? '#991b1b' : 'inherit' }};">
                        @if($row['is_disburse'])
                            —
                        @elseif($dendaAmount > 0)
                            {{ number_format($dendaAmount, 2, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center font-mono" style="font-size:6.5pt;">
                        @if(!$row['is_disburse'] && isset($row['payment_obj']) && $row['payment_obj'])
                            {{ \Carbon\Carbon::parse($row['payment_obj']->trx_date)->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right font-mono">
                        @if($row['is_disburse'])
                            {{ number_format($row['amount'], 2, ',', '.') }}
                        @elseif($row['payment'] > 0)
                            {{ number_format($row['payment'], 2, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right font-mono">{{ number_format($row['balance'], 2, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Totals row --}}
            <tr class="row-total">
                <td colspan="5" class="text-right" style="padding-right:6pt; letter-spacing:0.3px; font-size:6.5pt; text-transform:uppercase;">
                    Total
                </td>
                <td class="text-right font-mono">{{ number_format($totalAmount, 2, ',', '.') }}</td>
                <td class="text-center">—</td>
                <td class="text-right font-mono">{{ number_format($totalPenalty, 2, ',', '.') }}</td>
                <td class="text-center">—</td>
                <td class="text-right font-mono">{{ number_format($totalPayment, 2, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format(max(0, $loan->outstanding), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Legend --}}
    <div style="margin-top:6pt; font-size:7pt; color:#555;">
        Keterangan: &nbsp;
        <span class="badge-l">L</span> = Lunas / Dibayar &nbsp;&nbsp;
        <span class="badge-o">O</span> = Open / Belum Dibayar
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
            <div>Mengetahui,</div>
            <div class="sig-line">( ___________________ )</div>
        </div>
    </div>

    <div class="footer">
        Dicetak: {{ now()->format('d M Y H:i') }} &mdash; KSP-SMM
    </div>

</body>
</html>
