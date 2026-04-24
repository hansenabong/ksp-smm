<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Deposito</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 1.2cm 1.5cm; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; color: #000; line-height: 1.4; }
        .company { font-size: 11pt; font-weight: bold; text-align: center; }
        .module-tag { font-size: 7.5pt; text-align: center; color: #555; margin-top: 1pt; }
        .divider-thick { border-top: 2pt solid #000; margin: 3pt 0; }
        h1.title { text-align: center; font-size: 12pt; font-weight: bold; text-decoration: underline; margin: 5pt 0 4pt 0; }
        .meta { font-size: 8pt; margin: 4pt 0 6pt 0; color: #333; }
        .meta span { margin-right: 18pt; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 4pt; }
        table.lines th {
            background-color: #1f2937; color: #fff; font-size: 8pt; font-weight: bold;
            text-align: center; padding: 4pt 4pt; border: 0.5pt solid #374151;
        }
        table.lines td { font-size: 8pt; padding: 3.5pt 4pt; border: 0.5pt solid #d1d5db; vertical-align: top; }
        table.lines tr.total-row td { font-weight: bold; background-color: #f3f4f6; border-top: 1pt solid #374151; }
        .right { text-align: right; }
        .center { text-align: center; }
        .mono { font-family: 'Courier New', monospace; font-size: 7.5pt; }
        .footer { margin-top: 14pt; font-size: 7.5pt; color: #555; text-align: right; }
    </style>
</head>
<body>
    <div class="company">KSP — Koperasi Simpan Pinjam</div>
    <div class="module-tag">Daftar Kontrak Deposito</div>
    <div class="divider-thick"></div>

    <h1 class="title">DAFTAR DEPOSITO</h1>

    <div class="meta">
        @if($filterStatus)<span><strong>Status:</strong> {{ $filterStatus }}</span>@endif
        @if($filterMember)<span><strong>Anggota:</strong> {{ $filterMember }}</span>@endif
        @if($filterProduct)<span><strong>Produk:</strong> {{ $filterProduct }}</span>@endif
        <span><strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:11%">Ref</th>
                <th>Anggota</th>
                <th>Produk</th>
                <th style="width:12%" class="right">Pokok (Rp)</th>
                <th style="width:8%" class="right">Bunga/Th</th>
                <th style="width:6%" class="center">Tenor</th>
                <th style="width:9%" class="center">Mulai</th>
                <th style="width:9%" class="center">Jatuh Tempo</th>
                <th style="width:8%" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPrincipal = 0; @endphp
            @forelse($depositos as $i => $d)
                @php $totalPrincipal += (int)$d->principal_amount; @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="mono">{{ $d->reference_code }}</td>
                    <td>
                        {{ $d->depositorDisplayName() }}<br>
                        <span style="color:#888; font-size:7pt">{{ $d->member?->member_no ?? 'Non-Anggota' }}</span>
                    </td>
                    <td>{{ $d->savingProduct->name }}</td>
                    <td class="right">{{ number_format($d->principal_amount, 0, ',', '.') }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format((float)$d->interest_rate_pct, 2, ',', '.'), '0'), ',') }}%</td>
                    <td class="center">{{ $d->term_months }} bln</td>
                    <td class="center">{{ $d->start_date->format('d/m/Y') }}</td>
                    <td class="center">{{ $d->maturity_date->format('d/m/Y') }}</td>
                    <td class="center">{{ $d->displayStatus() }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="center" style="color:#888">Belum ada data deposito.</td></tr>
            @endforelse
            @if($depositos->isNotEmpty())
                <tr class="total-row">
                    <td colspan="4" class="right"><strong>Total</strong></td>
                    <td class="right"><strong>{{ number_format($totalPrincipal, 0, ',', '.') }}</strong></td>
                    <td colspan="5"></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">Total {{ count($depositos) }} kontrak</div>
</body>
</html>
