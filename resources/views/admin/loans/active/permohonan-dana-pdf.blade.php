<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }

    .outer-border { border: 1.5pt solid #000; }

    .title-bar { border-bottom: 1.5pt solid #000; padding: 5pt 8pt; text-align: center; }
    .title-bar .app { font-weight: bold; font-size: 12pt; text-transform: uppercase; letter-spacing: 1pt; }
    .title-bar .doc { font-weight: bold; font-size: 10pt; text-transform: uppercase; letter-spacing: 1pt; }

    table.layout { width: 100%; border-collapse: collapse; }
    table.layout td, table.layout th { padding: 4pt 6pt; }

    .row-border-b { border-bottom: 1pt solid #000; }
    .cell-border-r { border-right: 1pt solid #000; }

    .label-ul { font-weight: bold; text-transform: uppercase; text-decoration: underline; }
    .mono { font-family: 'Courier New', monospace; }

    .section-title { font-weight: bold; font-size: 9pt; text-transform: uppercase; margin-bottom: 4pt; }
    .sig-table { width: 100%; border-collapse: collapse; }
    .sig-table td { border-top: 1.5pt solid #000; padding: 4pt 6pt; text-align: center; vertical-align: top; height: 60pt; }
    .sig-table .sig-line { border-top: 1pt solid #555; margin-top: 40pt; padding-top: 2pt; font-size: 8pt; }

    .fee-grid { width: 100%; border-collapse: collapse; }
    .fee-grid td { padding: 1.5pt 4pt; font-size: 9pt; vertical-align: top; }
    .fee-grid .col-label { width: 38%; }
    .fee-grid .col-sep   { width: 5%; }
    .fee-grid .col-val   { width: 57%; text-align: right; font-family: 'Courier New', monospace; }

    .total-row { border-top: 1pt solid #000; margin-top: 4pt; padding-top: 3pt; }
    .bersih-row { border-bottom: 1.5pt solid #000; padding: 4pt 6pt; }
</style>
</head>
<body>

@php
    $member  = $loan->member;
    $tgl     = $loan->disbursed_date instanceof \Carbon\Carbon
                ? $loan->disbursed_date
                : \Carbon\Carbon::parse($loan->disbursed_date);

    $noPk = $loan->application?->notaris_deed_number
         ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . $tgl->format('Y'));

    $objectFinance = $loan->application?->object_finance ?? '-';

    $feeLabels = [
        'biaya_administrasi' => 'Biaya Administrasi',
        'biaya_provisi'      => 'Biaya Provisi',
        'biaya_notaris'      => 'Biaya Notaris',
        'asuransi'           => 'Asuransi',
        'pengecekan'         => 'Pengecekan',
        'ploting'            => 'Ploting',
        'roya'               => 'Roya',
        'pnpb'               => 'PNPB',
        'hold_imb'           => 'Hold IMB',
        'hold_pbb'           => 'Hold PBB',
        'hold_angsuran'      => 'Hold Angsuran',
        'bphtb'              => 'BPHTB',
        'pph'                => 'PPh',
    ];

    $leftFeeKeys  = ['biaya_administrasi','biaya_provisi','biaya_notaris','asuransi','pengecekan','ploting','roya','pnpb','hold_imb','hold_pbb'];
    $rightFeeKeys = ['hold_angsuran','bphtb','pph'];

    // Exclude hold_angsuran_ke (count, not rupiah) from the total to prevent 2-rupiah discrepancy.
    $jumlahBiaya     = collect($fees)->except(['hold_angsuran_ke'])->sum();
    $pencairanBersih = (int)$loan->principal - $jumlahBiaya;
    $totalBunga      = (int)$loan->interest_monthly * (int)$loan->tenor_months;

    $cashName = $loan->disbursedCashAccount?->name ?? '-';
    $cashType = $loan->disbursedCashAccount?->type ?? 'CASH';

    // Prefer manual bank entry over cash-account fallback
    $bank = $bank ?? [];
    $bankName = $bank['bank_name'] ?? ($cashType === 'BANK' ? $cashName : '');
    $bankAcNo = $bank['ac_no']     ?? '';
    $bankAtas = $bank['atas_nama'] ?? ($member?->name ?? '');
@endphp

<div class="outer-border">

    {{-- Title --}}
    @php
        $__logo    = public_path('images/logo-ksp.jpeg');
        $__logoSrc = file_exists($__logo) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($__logo)) : '';
    @endphp
    <div class="title-bar" style="display:flex; align-items:center; gap:10pt;">
        @if($__logoSrc)
        <img src="{{ $__logoSrc }}" alt="KSP SMM" style="width:44pt; height:44pt; object-fit:contain;">
        @endif
        <div>
            <div class="app">KSP SARANA MITRA MANDIRI</div>
            <div class="doc">Permohonan Dana</div>
        </div>
    </div>

    {{-- Nasabah + No/Date --}}
    <table class="layout row-border-b">
        <tr>
            <td style="width:70%;" class="cell-border-r">
                <strong>NASABAH</strong> : {{ $member?->name ?? '-' }}
            </td>
            <td style="width:30%; padding:0;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:35%; border-bottom:1pt solid #000; border-right:1pt solid #000; padding:3pt 5pt;">No</td>
                        <td style="border-bottom:1pt solid #000; padding:3pt 5pt;" class="mono">{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td style="border-right:1pt solid #000; padding:3pt 5pt;">Date</td>
                        <td style="padding:3pt 5pt;">{{ $tgl->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- PK / Pembiayaan / Pembayaran --}}
    <table class="layout" style="border-bottom:1.5pt solid #000;">
        <tr class="row-border-b" style="border-bottom:1pt solid #aaa;">
            <td style="width:42%;"><span class="label-ul">No. Perjanjian Kredit</span></td>
            <td style="width:3%;">:</td>
            <td class="mono">{{ $noPk }}</td>
        </tr>
        <tr class="row-border-b" style="border-bottom:1pt solid #aaa;">
            <td><span class="label-ul">Pembiayaan Yang Disetujui</span></td>
            <td>:</td>
            <td class="mono">Rp {{ number_format($loan->principal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="vertical-align:top; padding-top:5pt;"><span class="label-ul">Pembayaran</span></td>
            <td style="vertical-align:top; padding-top:5pt;">:</td>
            <td style="padding:4pt 0;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="width:50%; vertical-align:top;">
                            <table style="width:100%; border-collapse:collapse; font-size:9pt;">
                                <tr>
                                    <td style="width:40%;">Amount</td>
                                    <td style="width:5%;">:</td>
                                    <td style="border:1pt solid #555; padding:2pt 4pt;" class="mono">Rp {{ number_format($loan->principal, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width:50%; vertical-align:top; font-size:9pt;">
                            <div style="font-weight:bold; text-transform:uppercase; font-size:9pt; margin-bottom:3pt;">Dana Yang Diminta</div>
                            <table style="width:100%; border-collapse:collapse;">
                                <tr>
                                    <td style="width:35%;">Bank</td>
                                    <td style="width:5%;">:</td>
                                    <td>{{ $bankName !== '' ? $bankName : '-' }}</td>
                                </tr>
                                <tr>
                                    <td>A/C No.</td>
                                    <td>:</td>
                                    <td style="border:1pt solid #555; padding:2pt 4pt;" class="mono">{{ $bankAcNo }}</td>
                                </tr>
                                <tr>
                                    <td>Atas Nama</td>
                                    <td>:</td>
                                    <td>{{ $bankAtas !== '' ? $bankAtas : '-' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Loan detail grid --}}
    <table class="layout" style="border-bottom:1.5pt solid #000; font-size:9pt;">
        <tr style="border-bottom:1pt solid #ccc;">
            <td style="width:50%;">Jenis Fasilitas : Pembiayaan</td>
            <td style="width:50%;">Produk : {{ $loan->loanProduct?->name ?? '-' }}</td>
        </tr>
        <tr style="border-bottom:1pt solid #ccc;">
            <td>Objek Pembiayaan : {{ $objectFinance }}</td>
            <td>No. Anggota : <span class="mono">{{ $loan->member?->member_no ?? '-' }}</span></td>
        </tr>
        <tr style="border-bottom:1pt solid #ccc;">
            <td>Jangka Waktu : <strong>{{ $loan->tenor_months }} Bulan</strong></td>
            <td>Angsuran / Bulan : <span class="mono">Rp {{ number_format($loan->monthly_installment, 0, ',', '.') }}</span></td>
        </tr>
        <tr style="border-bottom:1pt solid #ccc;">
            <td>Suku Bunga : {{ $loan->interest_monthly_percent }}% / bln</td>
            <td>Bunga (total) : <span class="mono">Rp {{ number_format($totalBunga, 0, ',', '.') }}</span></td>
        </tr>
        <tr style="border-bottom:1pt solid #ccc;">
            <td>Nilai Pembiayaan : <strong class="mono">Rp {{ number_format($loan->principal, 0, ',', '.') }}</strong></td>
            <td></td>
        </tr>
        <tr>
            <td>Pencairan Bersih : <strong class="mono">Rp {{ number_format(max(0, $pencairanBersih), 0, ',', '.') }}</strong></td>
            <td></td>
        </tr>
    </table>

    {{-- Biaya-biaya --}}
    <div style="border-bottom:1.5pt solid #000; padding:5pt 6pt;">
        <div class="section-title">Biaya-Biaya Lainnya</div>
        <table style="width:100%; border-collapse:collapse; font-size:9pt;">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    @foreach($leftFeeKeys as $key)
                    <table class="fee-grid">
                        <tr>
                            <td class="col-label">{{ $feeLabels[$key] }}</td>
                            <td class="col-sep">: Rp.</td>
                            <td class="col-val">{{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '' }}</td>
                        </tr>
                    </table>
                    @endforeach
                </td>
                <td style="width:50%; vertical-align:top;">
                    @foreach($rightFeeKeys as $key)
                    <table class="fee-grid">
                        <tr>
                            <td class="col-label">
                                {{ $feeLabels[$key] }}@if($key === 'hold_angsuran' && !empty($fees['hold_angsuran_ke'])) ({{ $fees['hold_angsuran_ke'] }} angsuran)@endif
                            </td>
                            <td class="col-sep">: Rp</td>
                            <td class="col-val">{{ isset($fees[$key]) ? number_format($fees[$key], 0, ',', '.') : '' }}</td>
                        </tr>
                    </table>
                    @endforeach
                </td>
            </tr>
        </table>
        <table style="width:100%; border-collapse:collapse; margin-top:4pt; border-top:1pt solid #000; padding-top:3pt;">
            <tr>
                <td style="text-align:right; font-weight:bold; font-size:9pt; padding-top:3pt;">
                    Jumlah Biaya : Rp. <span class="mono">{{ number_format($jumlahBiaya, 0, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Pencairan Bersih --}}
    <div style="border-bottom:1.5pt solid #000; padding:4pt 6pt;">
        <strong>Pencairan Bersih</strong>
        <table style="width:100%; border-collapse:collapse; font-size:9pt; margin-top:3pt;">
            <tr>
                <td style="width:40%;">Jumlah potongan</td>
                <td style="width:5%;">:</td>
                <td><span class="mono">Rp {{ number_format($jumlahBiaya, 0, ',', '.') }}</span></td>
            </tr>
            <tr>
                <td>Jumlah di setor</td>
                <td>:</td>
                <td><strong class="mono">Rp {{ number_format(max(0, $pencairanBersih), 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- Signature table --}}
    <table class="sig-table" style="border-collapse:collapse;">
        <tr>
            <td style="border-right:1pt solid #000; width:25%;">&nbsp;</td>
            <td style="border-right:1pt solid #000; width:25%;">
                <div style="font-weight:bold; font-size:9pt; text-align:center;">Di buat oleh</div>
                <div class="sig-line">{{ $loan->creator?->name ?? '_______________' }}</div>
            </td>
            <td style="border-right:1pt solid #000; width:25%;">
                <div style="font-weight:bold; font-size:9pt; text-align:center;">Diperiksa oleh</div>
                <div class="sig-line">&nbsp;</div>
            </td>
            <td style="width:25%;">
                <div style="font-weight:bold; font-size:9pt; text-align:center;">Disetujui Oleh</div>
                <div class="sig-line">&nbsp;</div>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
