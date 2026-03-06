<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Amortization Schedule #{{ $loan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 landscape;
            margin: 1cm 1.2cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            margin-bottom: 6pt;
        }
        .header .company {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header .module {
            font-size: 8pt;
            color: #555;
            margin-bottom: 4pt;
        }
        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 6pt;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 6pt;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1pt 4pt;
            font-size: 8pt;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 130pt;
            color: #333;
        }

        .divider {
            border-top: 1.5pt solid #000;
            margin: 4pt 0;
        }

        table.schedule {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4pt;
        }
        table.schedule th {
            background-color: #1f2937;
            color: #fff;
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            padding: 3pt 3pt;
            border: 0.5pt solid #374151;
            white-space: nowrap;
        }
        table.schedule td {
            font-size: 7pt;
            padding: 2pt 3pt;
            border: 0.5pt solid #d1d5db;
            vertical-align: middle;
        }
        table.schedule tr.row-zero td {
            background-color: #f3f4f6;
        }
        table.schedule tr.subtotal td {
            background-color: #dbeafe;
            font-weight: bold;
            font-size: 7pt;
        }
        table.schedule tr.grand-total td {
            background-color: #1f2937;
            color: #fff;
            font-weight: bold;
        }
        table.schedule tr.paid td {
            background-color: #f0fdf4;
        }
        table.schedule tr.overdue td {
            background-color: #fef2f2;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        .footer {
            margin-top: 6pt;
            font-size: 7pt;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">KSP-SMM — Sistem Manajemen Koperasi Simpan Pinjam</div>
        <div class="module">Module : Amortisasi Schedule</div>
        <h1>Amortization Schedule</h1>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Agreement No.</td>
            <td>: {{ $loan->id }}</td>
            <td class="meta-label">Int.Method</td>
            <td>: Flat Rate</td>
        </tr>
        <tr>
            <td class="meta-label">Name</td>
            <td>: {{ $loan->member->name }}</td>
            <td class="meta-label">Produk</td>
            <td>: {{ $loan->loanProduct->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Cair</td>
            <td>: {{ \Carbon\Carbon::parse($loan->disbursed_date)->format('d M Y') }}</td>
            <td class="meta-label">Tenor</td>
            <td>: {{ $loan->tenor_months }} bulan</td>
        </tr>
        <tr>
            <td class="meta-label">Object Finance</td>
            <td colspan="3">: {{ $loan->application->object_finance ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="schedule">
        <thead>
            <tr>
                <th style="width:22pt">Per</th>
                <th style="width:52pt">Date Due</th>
                <th style="width:62pt">Loan Payment</th>
                <th style="width:62pt">Loan Income</th>
                <th style="width:62pt">Recovery</th>
                <th style="width:68pt">Outstanding<br>Principal</th>
                <th style="width:68pt">Unearned<br>Loan Income</th>
                <th style="width:68pt">Loan Contract<br>Receivable</th>
                <th style="width:42pt">BLV<br>% of Eq.Cost</th>
            </tr>
        </thead>
        <tbody>
            @php
                $today = \Carbon\Carbon::today();
                $annualLoanPayment = 0;
                $annualLoanIncome  = 0;
                $annualRecovery    = 0;
                $totalLoanPayment  = 0;
                $totalLoanIncome   = 0;
                $totalRecovery     = 0;
            @endphp

            @foreach($schedule as $row)
                @if(!$row['is_row_zero'])
                    @php
                        $annualLoanPayment += $row['loan_payment'];
                        $annualLoanIncome  += $row['loan_income'];
                        $annualRecovery    += $row['recovery'];
                        $totalLoanPayment  += $row['loan_payment'];
                        $totalLoanIncome   += $row['loan_income'];
                        $totalRecovery     += $row['recovery'];
                    @endphp
                @endif

                @php
                    $isPaid    = isset($paidMap[$row['per']]);
                    $isOverdue = !$row['is_row_zero'] && !$isPaid && $row['due_date']->lt($today);
                    $trClass   = $row['is_row_zero'] ? 'row-zero' : ($isPaid ? 'paid' : ($isOverdue ? 'overdue' : ''));
                @endphp

                <tr class="{{ $trClass }}">
                    <td class="text-center">{{ $row['per'] }}</td>
                    <td class="text-center">
                        {{ $row['due_date']->format('d M Y') }}
                    </td>
                    <td class="text-right font-mono">
                        @if($row['is_row_zero']) 0,00 @else {{ number_format($row['loan_payment'], 2, ',', '.') }} @endif
                    </td>
                    <td class="text-right font-mono">
                        @if($row['is_row_zero']) 0,00 @else {{ number_format($row['loan_income'], 2, ',', '.') }} @endif
                    </td>
                    <td class="text-right font-mono">
                        @if($row['is_row_zero']) 0,00 @else {{ number_format($row['recovery'], 2, ',', '.') }} @endif
                    </td>
                    <td class="text-right font-mono">{{ number_format($row['outstanding_principal'], 2, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($row['unearned_loan_income'], 2, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($row['loan_contract_receivable'], 2, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($row['blv_pct'], 4, ',', '.') }}</td>
                </tr>

                {{-- Annual subtotal after every 12th installment --}}
                @if(!$row['is_row_zero'] && $row['per'] % 12 === 0)
                    <tr class="subtotal">
                        <td colspan="2" class="text-center" style="font-size:6.5pt; text-transform:uppercase; letter-spacing:0.3px;">
                            Subtotal Tahun ke-{{ intdiv($row['per'], 12) }}
                        </td>
                        <td class="text-right font-mono">{{ number_format($annualLoanPayment, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($annualLoanIncome, 2, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ number_format($annualRecovery, 2, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                    @php $annualLoanPayment = $annualLoanIncome = $annualRecovery = 0; @endphp
                @endif
            @endforeach

            @if($loan->tenor_months % 12 !== 0 && $annualLoanPayment > 0)
                <tr class="subtotal">
                    <td colspan="2" class="text-center" style="font-size:6.5pt; text-transform:uppercase; letter-spacing:0.3px;">
                        Subtotal Tahun ke-{{ ceil($loan->tenor_months / 12) }}
                    </td>
                    <td class="text-right font-mono">{{ number_format($annualLoanPayment, 2, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($annualLoanIncome, 2, ',', '.') }}</td>
                    <td class="text-right font-mono">{{ number_format($annualRecovery, 2, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
            @endif

            <tr class="grand-total">
                <td colspan="2" class="text-center" style="font-size:6.5pt; text-transform:uppercase; letter-spacing:0.5px;">Total</td>
                <td class="text-right font-mono">{{ number_format($totalLoanPayment, 2, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($totalLoanIncome, 2, ',', '.') }}</td>
                <td class="text-right font-mono">{{ number_format($totalRecovery, 2, ',', '.') }}</td>
                <td class="text-right font-mono">0,00</td>
                <td class="text-right font-mono">0,00</td>
                <td class="text-right font-mono">0,00</td>
                <td class="text-right font-mono">0,0000</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak: {{ \Carbon\Carbon::now()->format('d M Y H:i') }} &mdash; KSP-SMM
    </div>

</body>
</html>
