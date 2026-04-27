<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Amortisasi #{{ $loan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 1.2cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; color: #000; }

        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }
        .kop-company { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .kop-sub { font-size: 8pt; margin-top: 1pt; color: #444; }
        .divider-double { border-top: 2.5pt solid #000; border-bottom: 1pt solid #000; height: 3pt; margin: 4pt 0 8pt 0; }
        .doc-title { text-align: center; font-size: 12pt; font-weight: bold; text-decoration: underline; letter-spacing: 1px; margin-bottom: 10pt; }

        .info-table { border-collapse: collapse; margin-bottom: 8pt; }
        .info-table td { padding: 1.5pt 0; font-size: 8.5pt; vertical-align: top; }
        .info-label { width: 90pt; color: #444; }
        .info-sep { width: 10pt; }

        table.amort { width: 100%; border-collapse: collapse; }
        table.amort th {
            background: #1f2937;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 4pt 3pt;
            border: 0.5pt solid #374151;
        }
        table.amort td {
            border: 0.5pt solid #d1d5db;
            padding: 2.5pt 3pt;
            font-size: 8pt;
            vertical-align: middle;
        }
        table.amort td.num { text-align: right; font-family: 'Courier New', monospace; }
        table.amort td.ctr { text-align: center; }

        .row-zero  { background: #f9fafb; }
        .row-paid  { background: #f0fdf4; }
        .row-due   { background: #fef2f2; }
        .row-sub   { background: #eff6ff; font-weight: bold; }
        .row-total { background: #1f2937; color: #fff; font-weight: bold; }
        .row-total td { border-color: #374151; }
    </style>
</head>
<body>
    @php
        $logo    = public_path('images/logo-ksp.jpeg');
        $logoSrc = file_exists($logo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logo)) : '';

        $member   = $loan->member;
        $disbursed = \Carbon\Carbon::parse($loan->disbursed_date);
        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . $disbursed->format('Y'));

        $today = \Carbon\Carbon::today();
        $annualLP = $annualLI = $annualRec = 0;
        $totalLP  = $totalLI  = $totalRec  = 0;
    @endphp

    {{-- KOP --}}
    <table class="kop-table">
        <tr>
            <td style="width:60pt; vertical-align:middle; text-align:left; padding-right:6pt;">
                @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="KSP SMM" style="width:52pt; height:52pt;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div class="kop-company">KSP SARANA MITRA MANDIRI</div>
                <div class="kop-sub">Jalan RS Fatmawati Komplek Duta Mas Blok B1 No.16, Kel. Cipete Utara, Kec. Kebayoran Baru, Jakarta Selatan 12150</div>
            </td>
            <td style="width:60pt;"></td>
        </tr>
    </table>
    <div class="divider-double"></div>
    <div class="doc-title">TABEL AMORTISASI</div>

    {{-- INFO --}}
    <table class="info-table" style="width:100%; margin-bottom:10pt;">
        <tr>
            <td class="info-label">No. Kontrak</td><td class="info-sep">:</td>
            <td style="width:30%;">{{ $noPk }}</td>
            <td style="width:20pt;"></td>
            <td class="info-label">Anggota</td><td class="info-sep">:</td>
            <td>{{ $member?->name ?? '-' }} ({{ $member?->member_no ?? '-' }})</td>
        </tr>
        <tr>
            <td class="info-label">Plafon</td><td class="info-sep">:</td>
            <td>Rp {{ number_format($loan->principal, 0, ',', '.') }}</td>
            <td></td>
            <td class="info-label">Tenor</td><td class="info-sep">:</td>
            <td>{{ $loan->tenor_months }} bulan</td>
        </tr>
        <tr>
            <td class="info-label">Bunga/bln</td><td class="info-sep">:</td>
            <td>{{ $loan->interest_monthly_percent }}%</td>
            <td></td>
            <td class="info-label">Tgl. Cair</td><td class="info-sep">:</td>
            <td>{{ $disbursed->format('d M Y') }}</td>
        </tr>
    </table>

    {{-- AMORTISASI TABLE --}}
    <table class="amort">
        <thead>
            <tr>
                <th style="width:22pt;">Per</th>
                <th style="width:52pt;">Tgl Jatuh Tempo</th>
                <th style="width:68pt;">Loan Payment</th>
                <th style="width:68pt;">Loan Income</th>
                <th style="width:68pt;">Recovery</th>
                <th style="width:72pt;">Outstanding<br>Principal</th>
                <th style="width:72pt;">Unearned<br>Loan Income</th>
                <th style="width:72pt;">Loan Contract<br>Receivable</th>
                <th style="width:50pt;">BLV<br>% of Eq.Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule as $row)
                @if(!$row['is_row_zero'])
                    @php
                        $annualLP  += $row['loan_payment'];
                        $annualLI  += $row['loan_income'];
                        $annualRec += $row['recovery'];
                        $totalLP   += $row['loan_payment'];
                        $totalLI   += $row['loan_income'];
                        $totalRec  += $row['recovery'];
                    @endphp
                @endif

                @php
                    $isPaid    = isset($paidMap[$row['per']]);
                    $isOverdue = !$row['is_row_zero'] && !$isPaid && $row['due_date']->lt($today);
                    $rowCls = $row['is_row_zero'] ? 'row-zero'
                        : ($isPaid ? 'row-paid' : ($isOverdue ? 'row-due' : ''));
                @endphp

                <tr class="{{ $rowCls }}">
                    <td class="ctr">{{ $row['per'] }}</td>
                    <td class="ctr" style="font-size:7.5pt;">{{ $row['due_date']->format('d/m/Y') }}</td>
                    <td class="num">@if(!$row['is_row_zero']){{ number_format($row['loan_payment'], 2, ',', '.') }}@else —@endif</td>
                    <td class="num">@if(!$row['is_row_zero']){{ number_format($row['loan_income'], 2, ',', '.') }}@else —@endif</td>
                    <td class="num">@if(!$row['is_row_zero']){{ number_format($row['recovery'], 2, ',', '.') }}@else —@endif</td>
                    <td class="num">{{ number_format($row['outstanding_principal'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($row['unearned_loan_income'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($row['loan_contract_receivable'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format($row['blv_pct'], 4, ',', '.') }}</td>
                </tr>

                @if(!$row['is_row_zero'] && $row['per'] % 12 === 0)
                <tr class="row-sub">
                    <td colspan="2" class="ctr" style="font-size:7pt; text-transform:uppercase; letter-spacing:0.3px; color:#1e40af;">
                        Subtotal Tahun ke-{{ intdiv($row['per'], 12) }}
                    </td>
                    <td class="num" style="color:#1e40af;">{{ number_format($annualLP, 2, ',', '.') }}</td>
                    <td class="num" style="color:#1e40af;">{{ number_format($annualLI, 2, ',', '.') }}</td>
                    <td class="num" style="color:#1e40af;">{{ number_format($annualRec, 2, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
                @php $annualLP = $annualLI = $annualRec = 0; @endphp
                @endif
            @endforeach

            @if($loan->tenor_months % 12 !== 0 && $annualLP > 0)
            <tr class="row-sub">
                <td colspan="2" class="ctr" style="font-size:7pt; text-transform:uppercase; letter-spacing:0.3px; color:#1e40af;">
                    Subtotal Tahun ke-{{ ceil($loan->tenor_months / 12) }}
                </td>
                <td class="num" style="color:#1e40af;">{{ number_format($annualLP, 2, ',', '.') }}</td>
                <td class="num" style="color:#1e40af;">{{ number_format($annualLI, 2, ',', '.') }}</td>
                <td class="num" style="color:#1e40af;">{{ number_format($annualRec, 2, ',', '.') }}</td>
                <td colspan="4"></td>
            </tr>
            @endif

            <tr class="row-total">
                <td colspan="2" class="ctr" style="font-size:7pt; text-transform:uppercase; letter-spacing:0.3px;">Total</td>
                <td class="num">{{ number_format($totalLP, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totalLI, 2, ',', '.') }}</td>
                <td class="num">{{ number_format($totalRec, 2, ',', '.') }}</td>
                <td class="num">0,00</td>
                <td class="num">0,00</td>
                <td class="num">0,00</td>
                <td class="num">0,0000</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:6pt; font-size:7pt; color:#555;">
        Dicetak: {{ now()->format('d M Y H:i') }} &mdash; KSP-SMM
    </div>
</body>
</html>
