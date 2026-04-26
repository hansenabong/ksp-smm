<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Angsuran #{{ $loan->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 1.5cm 2cm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        /* Kop surat */
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }

        .kop-company {
            font-size: 15pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-sub { font-size: 9pt; margin-top: 2pt; }

        .divider-double {
            border-top: 2.5pt solid #000;
            border-bottom: 1pt solid #000;
            height: 3pt;
            margin: 5pt 0 10pt 0;
        }

        /* Title */
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1px;
            margin-bottom: 14pt;
        }

        /* Info fields */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 14pt; }
        .info-table td { padding: 2pt 0; font-size: 10pt; vertical-align: top; }
        .info-label { width: 100pt; }
        .info-sep   { width: 10pt; }

        /* Angsuran table */
        table.jadwal {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
        }
        table.jadwal th {
            border: 1pt solid #000;
            padding: 5pt 4pt;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            background: #fff;
        }
        table.jadwal th .subheader { font-size: 9pt; font-weight: bold; }
        table.jadwal td {
            border: 1pt solid #000;
            padding: 4pt;
            font-size: 10pt;
            text-align: center;
            height: 18pt;
        }
        table.jadwal td.amount { text-align: right; font-family: 'Courier New', monospace; }

        /* Signature */
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 10pt; }
        .sig-table td { width: 50%; vertical-align: top; padding: 0 10pt; text-align: center; font-size: 10pt; }
        .sig-line { border-top: 1pt solid #000; margin-top: 50pt; padding-top: 4pt; }
    </style>
</head>
<body>

    @php
        $logo    = public_path('images/logo-ksp.jpeg');
        $logoSrc = file_exists($logo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logo)) : '';

        $member       = $loan->member;
        $fd           = $loan->application?->form_data ?? [];
        $disbursed    = \Carbon\Carbon::parse($loan->disbursed_date);
        $tenor        = (int) $loan->tenor_months;
        $principal    = (int) $loan->principal;
        $installment  = (int) $loan->monthly_installment;
        $lastPrincipal = $principal - ((int)$loan->principal_monthly * ($tenor - 1));
        $lastInstallment = $lastPrincipal + (int)$loan->interest_monthly + (int)$loan->insurance_monthly;

        $alamat = trim(collect([
            $fd['alamat']    ?? $member?->address ?? '',
            isset($fd['rt'], $fd['rw']) ? 'RT.'.$fd['rt'].' RW.'.$fd['rw'] : '',
            $fd['kelurahan'] ?? '',
            $fd['kecamatan'] ?? '',
            $fd['kota']      ?? '',
        ])->filter()->implode(', '));

        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . $disbursed->format('Y'));

        // Disbursement fees (biaya pencairan) — net realisasi
        $fees = $loan->disbursement_fees ?? [];
        $jumlahBiaya = collect($fees)->except(['hold_angsuran_ke'])->sum();
        $realisasi   = $principal - $jumlahBiaya;

        $jatuhTempoAwal  = $disbursed->copy()->addMonths(1);
        $jatuhTempoAkhir = $disbursed->copy()->addMonths($tenor);

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
    @endphp

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td style="width:70pt; vertical-align:middle; text-align:left; padding-right:6pt;">
                @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="KSP SMM" style="width:62pt; height:62pt; object-fit:contain;">
                @endif
            </td>
            <td style="vertical-align:middle; text-align:center;">
                <div class="kop-company">KSP SARANA MITRA MANDIRI</div>
                <div class="kop-sub">Jalan RS Fatmawati Komplek Duta Mas Blok B1 No.16, Kel. Cipete Utara</div>
                <div class="kop-sub">Kec. Kebayoran Baru, Kota Jakarta Selatan 12150</div>
                <div class="kop-sub">No. Telepon : 0812 1635 217</div>
            </td>
            <td style="width:70pt;"></td>
        </tr>
    </table>
    <div class="divider-double"></div>

    {{-- TITLE --}}
    <div class="doc-title">JADWAL ANGSURAN ANGGOTA</div>

    {{-- INFO --}}
    <table class="info-table">
        <tr>
            <td class="info-label">No. Anggota</td>
            <td class="info-sep">:</td>
            <td>{{ $member?->member_no ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Nama</td>
            <td class="info-sep">:</td>
            <td>{{ $member?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Alamat</td>
            <td class="info-sep">:</td>
            <td>{{ $alamat ?: '-' }}</td>
        </tr>
    </table>

    <table class="info-table" style="margin-bottom:16pt;">
        <tr>
            <td class="info-label">No. Rekening</td>
            <td class="info-sep">:</td>
            <td style="width:45%;">{{ $noPk }}</td>
            <td style="width:20pt;"></td>
            <td class="info-label" style="width:80pt;">Jangka Waktu</td>
            <td class="info-sep">:</td>
            <td>{{ $tenor }} Bulan</td>
        </tr>
        <tr>
            <td class="info-label">Plafond</td>
            <td class="info-sep">:</td>
            <td>Rp {{ number_format($principal, 0, ',', '.') }}</td>
            <td></td>
            <td class="info-label">Realisasi</td>
            <td class="info-sep">:</td>
            <td>Rp {{ number_format($realisasi, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Jatuh Tempo</td>
            <td class="info-sep">:</td>
            <td colspan="5">
                {{ $jatuhTempoAwal->format('d') }} {{ $bulanId[$jatuhTempoAwal->month] }} {{ $jatuhTempoAwal->year }}
                s/d
                {{ $jatuhTempoAkhir->format('d') }} {{ $bulanId[$jatuhTempoAkhir->month] }} {{ $jatuhTempoAkhir->year }}
            </td>
        </tr>
    </table>

    {{-- JADWAL TABLE --}}
    <table class="jadwal">
        <thead>
            <tr>
                <th style="width:60pt;">Angsuran ke</th>
                <th>Tanggal<br><span class="subheader">Jatuh Tempo</span></th>
                <th style="width:140pt;">Jumlah Angsuran<br><span class="subheader">(Rp)</span></th>
            </tr>
        </thead>
        <tbody>
            @for($i = 1; $i <= $tenor; $i++)
                @php
                    $due   = $disbursed->copy()->addMonths($i);
                    $amt   = ($i === $tenor) ? $lastInstallment : $installment;
                @endphp
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $due->format('d') }} {{ $bulanId[$due->month] }} {{ $due->year }}</td>
                    <td class="amount">{{ number_format($amt, 0, ',', '.') }}</td>
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- SIGNATURE --}}
    <table class="sig-table">
        <tr>
            <td>
                <div>KSP SMM</div>
                <div class="sig-line">(...........................)</div>
            </td>
            <td>
                <div>Peminjam</div>
                <div class="sig-line">{{ $member?->name ?? '(.................................)' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
