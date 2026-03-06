<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kas — {{ $dateFrom }} s/d {{ $dateTo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 landscape; margin: 1cm 1.5cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
            line-height: 1.4;
        }

        .top-bar {
            display: table;
            width: 100%;
            margin-bottom: 4pt;
        }
        .top-bar .left  { display: table-cell; text-align: left;  font-size: 7.5pt; color: #555; }
        .top-bar .right { display: table-cell; text-align: right; font-size: 7.5pt; color: #555; }

        .company { font-size: 11pt; font-weight: bold; text-align: center; }
        .divider-thick { border-top: 2pt solid #000; margin: 3pt 0; }
        .divider-thin  { border-top: 0.5pt solid #888; margin: 3pt 0; }

        h1.title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 4pt 0;
        }

        .period { text-align: center; font-size: 8pt; margin-bottom: 6pt; color: #333; }

        /* Summary table */
        table.summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10pt;
        }
        table.summary th {
            background-color: #374151;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 3pt 5pt;
            border: 0.5pt solid #374151;
        }
        table.summary td {
            font-size: 7.5pt;
            padding: 3pt 5pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: middle;
        }
        table.summary tr.totals td {
            font-weight: bold;
            background-color: #f3f4f6;
        }

        /* Main transactions table */
        table.lines {
            width: 100%;
            border-collapse: collapse;
        }
        table.lines th {
            background-color: #1f2937;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 3pt 4pt;
            border: 0.5pt solid #374151;
        }
        table.lines td {
            font-size: 7.5pt;
            padding: 3pt 4pt;
            border: 0.5pt solid #e5e7eb;
            vertical-align: middle;
        }
        table.lines tr.income-row  td { background-color: #f0fdf4; }
        table.lines tr.expense-row td { background-color: #fff7f7; }
        table.lines tr.totals td {
            font-weight: bold;
            background-color: #f3f4f6;
            border-top: 1pt solid #6b7280;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }
        .badge-income   { color: #166534; font-weight: bold; }
        .badge-expense  { color: #991b1b; font-weight: bold; }
        .badge-transfer { color: #1e40af; font-weight: bold; }

        .footer { margin-top: 10pt; font-size: 7.5pt; color: #666; text-align: center; }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin: 8pt 0 3pt 0;
            border-bottom: 0.5pt solid #9ca3af;
            padding-bottom: 2pt;
        }
    </style>
</head>
<body>
    @php
        $totalIncome   = $transactions->where('type', 'INCOME')->sum('amount');
        $totalExpense  = $transactions->where('type', 'EXPENSE')->sum('amount');
        $totalTransfer = $transactions->where('type', 'TRANSFER')->sum('amount');
        $grandNet      = $totalIncome - $totalExpense;
    @endphp

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="left">{{ now()->format('d/m/y H:i') }}</div>
        <div class="right">KSP-SMM &mdash; Laporan Rekap Kas</div>
    </div>

    <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
    <div class="divider-thick"></div>
    <h1 class="title">Daftar Transaksi Kas</h1>
    <div class="period">Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</div>
    <div class="divider-thin"></div>

    {{-- Summary per Account --}}
    @if(count(array_filter($summary, fn($s) => $s['income'] + $s['expense'] + $s['transfer_in'] + $s['transfer_out'] > 0)) > 0)
    <div class="section-title">Ringkasan per Akun Kas</div>
    <table class="summary">
        <thead>
            <tr>
                <th style="width:30%">Akun Kas</th>
                <th style="width:16%">Pemasukan</th>
                <th style="width:16%">Pengeluaran</th>
                <th style="width:16%">Transfer Masuk</th>
                <th style="width:16%">Transfer Keluar</th>
                <th style="width:16%">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $s)
                @if($s['income'] + $s['expense'] + $s['transfer_in'] + $s['transfer_out'] > 0)
                <tr>
                    <td>{{ $s['name'] }}</td>
                    <td class="text-right">{{ number_format($s['income'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($s['expense'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($s['transfer_in'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($s['transfer_out'], 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="{{ $s['net'] < 0 ? 'color:#991b1b' : 'color:#166534' }}">
                        {{ number_format($s['net'], 0, ',', '.') }}
                    </td>
                </tr>
                @endif
            @endforeach
            <tr class="totals">
                <td>TOTAL</td>
                <td class="text-right">{{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalExpense, 0, ',', '.') }}</td>
                <td class="text-right">—</td>
                <td class="text-right">—</td>
                <td class="text-right font-mono" style="{{ $grandNet < 0 ? 'color:#991b1b' : 'color:#166534' }}">
                    {{ number_format($grandNet, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Transaction list --}}
    <div class="section-title">Detail Transaksi ({{ $transactions->count() }} data)</div>
    <table class="lines">
        <thead>
            <tr>
                <th style="width:20pt">No</th>
                <th style="width:55pt">Tanggal</th>
                <th style="width:70pt">Kode Sistem</th>
                <th style="width:65pt">Ref#</th>
                <th style="width:45pt">Tipe</th>
                <th>Dari / Untuk Kas</th>
                <th>Kategori</th>
                <th>Uraian</th>
                <th style="width:85pt" class="text-right">Jumlah (IDR)</th>
                <th style="width:65pt">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $i => $tx)
                @php
                    $rowClass = match($tx->type) {
                        'INCOME'   => 'income-row',
                        'EXPENSE'  => 'expense-row',
                        default    => '',
                    };
                    $typeLabel = match($tx->type) {
                        'INCOME'   => 'MASUK',
                        'EXPENSE'  => 'KELUAR',
                        'TRANSFER' => 'TRANSFER',
                        default    => $tx->type,
                    };
                    $typeClass = match($tx->type) {
                        'INCOME'   => 'badge-income',
                        'EXPENSE'  => 'badge-expense',
                        default    => 'badge-transfer',
                    };
                    if ($tx->type === 'INCOME') {
                        $cashName = $tx->toCashAccount?->name ?? '-';
                    } elseif ($tx->type === 'EXPENSE') {
                        $cashName = $tx->fromCashAccount?->name ?? '-';
                    } else {
                        $cashName = ($tx->fromCashAccount?->name ?? '-') . ' → ' . ($tx->toCashAccount?->name ?? '-');
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center font-mono">{{ \Carbon\Carbon::parse($tx->trx_date)->format('d/m/Y') }}</td>
                    <td class="font-mono" style="font-size:6.5pt">{{ $tx->code }}</td>
                    <td class="font-mono">{{ $tx->reference_code ?: '-' }}</td>
                    <td class="text-center {{ $typeClass }}">{{ $typeLabel }}</td>
                    <td>{{ $cashName }}</td>
                    <td>{{ $tx->category ?? $tx->cashCategory?->name ?? '-' }}</td>
                    <td>{{ $tx->description ?: '-' }}</td>
                    <td class="text-right font-mono">{{ number_format($tx->amount, 2, ',', '.') }}</td>
                    <td style="font-size:6.5pt">{{ $tx->creator?->name ?? $tx->creator?->email ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding:8pt; color:#9ca3af;">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            @endforelse
            @if($transactions->count() > 0)
            <tr class="totals">
                <td colspan="8" class="text-right" style="padding-right:6pt;">Total :</td>
                <td class="text-right font-mono">{{ number_format($transactions->sum('amount'), 2, ',', '.') }}</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="divider-thin" style="margin-top:8pt;"></div>
    <div class="footer">
        Dicetak: {{ now()->format('d M Y H:i') }} &mdash; KSP-SMM &mdash; Halaman 1
    </div>

</body>
</html>
