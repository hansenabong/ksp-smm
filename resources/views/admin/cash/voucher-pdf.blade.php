<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $voucherTitle ?? 'Voucher' }} — {{ $tx->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 1.5cm 2cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.4;
        }

        .top-bar {
            display: table;
            width: 100%;
            margin-bottom: 6pt;
        }
        .top-bar .left  { display: table-cell; text-align: left;  font-size: 8pt; color: #555; }
        .top-bar .right { display: table-cell; text-align: right; font-size: 8pt; color: #555; }

        .company { font-size: 11pt; font-weight: bold; }
        .module  { font-size: 8pt; color: #555; margin-bottom: 4pt; }

        .divider-thick { border-top: 2pt solid #000; margin: 4pt 0; }
        .divider-thin  { border-top: 0.5pt solid #888; margin: 4pt 0; }

        h1.title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 6pt 0;
        }

        table.info { width: 100%; border-collapse: collapse; margin: 6pt 0; }
        table.info td { padding: 2pt 4pt; font-size: 9pt; vertical-align: top; }
        .info-label { width: 130pt; font-weight: normal; color: #333; }
        .info-colon { width: 10pt; }

        .ref-block { display: table; width: 100%; }
        .ref-left  { display: table-cell; width: 55%; vertical-align: top; }
        .ref-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

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
            padding: 4pt 5pt;
            border: 0.5pt solid #374151;
        }
        table.lines td {
            font-size: 8.5pt;
            padding: 4pt 5pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: middle;
        }
        table.lines tr.total-row td {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-mono   { font-family: 'Courier New', Courier, monospace; }

        .footer { margin-top: 16pt; font-size: 8pt; color: #666; text-align: center; }

        .status-printed {
            display: inline-block;
            padding: 1pt 6pt;
            background-color: #f3f4f6;
            border: 0.5pt solid #9ca3af;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>
    @php
        $voucherTitle = match($tx->type) {
            'INCOME'   => 'Receipt Voucher (RV)',
            'EXPENSE'  => 'Payment Voucher (PV)',
            'TRANSFER' => 'Transfer Voucher (TV)',
            default    => 'Voucher',
        };
        $typeLabel = match($tx->type) {
            'INCOME'   => 'Pemasukan',
            'EXPENSE'  => 'Pengeluaran',
            'TRANSFER' => 'Transfer',
            default    => $tx->type,
        };
        $cashAccount = $tx->fromCashAccount ?? $tx->toCashAccount;
        $otherAccount = ($tx->type === 'TRANSFER') ? $tx->toCashAccount : null;
    @endphp

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="left">
            {{ now()->format('d/m/y H:i') }}
        </div>
        <div class="right">
            KSP-SMM &mdash; Module : {{ $voucherTitle }}
        </div>
    </div>

    {{-- Company + Title --}}
    <div style="text-align:center; margin-bottom:4pt;">
        <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
    </div>

    <div class="divider-thick"></div>

    <h1 class="title">{{ $voucherTitle }}</h1>

    <div class="divider-thick"></div>

    {{-- Info block (2-column) --}}
    <div class="ref-block" style="margin: 6pt 0;">
        <div class="ref-left">
            <table class="info">
                <tr>
                    <td class="info-label">From</td>
                    <td class="info-colon">:</td>
                    <td>{{ $tx->category ?? $tx->cashCategory?->name ?? '-' }}
                        @if($tx->description) <br><small style="color:#555">{{ $tx->description }}</small> @endif
                    </td>
                </tr>
                <tr>
                    <td class="info-label">By</td>
                    <td class="info-colon">:</td>
                    <td>{{ $cashAccount?->name ?? '-' }} ({{ $cashAccount?->type ?? '' }})</td>
                </tr>
                @if($otherAccount)
                <tr>
                    <td class="info-label">To</td>
                    <td class="info-colon">:</td>
                    <td>{{ $otherAccount->name }}</td>
                </tr>
                @endif
                <tr>
                    <td class="info-label">Trans.Dt</td>
                    <td class="info-colon">:</td>
                    <td>{{ \Carbon\Carbon::parse($tx->trx_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Entry.Dt</td>
                    <td class="info-colon">:</td>
                    <td>{{ $tx->created_at->format('d M Y H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="ref-right">
            <table class="info" style="float:right; text-align:right;">
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Ref # :</strong> {{ $tx->reference_code ?: '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Kode Sistem :</strong> <span class="font-mono">{{ $tx->code }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Status :</strong> <span class="status-printed">*Printed</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2pt 4pt; text-align:right;">
                        <strong>Amount :</strong> IDR {{ number_format($tx->amount, 2, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="divider-thin"></div>

    {{-- Line items table --}}
    <table class="lines">
        <thead>
            <tr>
                <th style="width:25pt">No</th>
                <th style="width:30pt">Modul</th>
                <th>Description</th>
                <th style="width:80pt" class="text-right">Amount</th>
                <th style="width:75pt">Document</th>
                <th style="width:80pt">Account</th>
                <th style="width:35pt">Branch</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center">{{ $typeLabel }}</td>
                <td>{{ $tx->category ?? $tx->cashCategory?->name ?? '-' }}
                    @if($tx->description) — {{ $tx->description }} @endif
                </td>
                <td class="text-right font-mono">{{ number_format($tx->amount, 2, ',', '.') }}</td>
                <td class="font-mono" style="font-size:7.5pt">{{ $tx->reference_code ?: '-' }}</td>
                <td style="font-size:7.5pt">{{ $cashAccount?->name ?? '-' }}</td>
                <td class="text-center">0000</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right" style="padding-right:8pt;">Total :</td>
                <td class="text-right font-mono">{{ number_format($tx->amount, 2, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="divider-thin" style="margin-top:10pt;"></div>

    <div class="footer">
        Dicetak: {{ now()->format('d M Y H:i') }} &mdash; KSP-SMM
    </div>

</body>
</html>
