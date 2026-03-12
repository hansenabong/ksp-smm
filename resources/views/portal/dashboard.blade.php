@extends('layouts.portal')

@section('content')
<div class="space-y-5">

    {{-- Greeting --}}
    <div>
        <h1 class="text-xl font-bold text-gray-800">Selamat datang, {{ $member->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">No. Anggota: <span class="font-semibold text-gray-700">{{ $member->member_no }}</span></p>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Simpanan</p>
            <p class="text-2xl font-bold text-indigo-700 mt-1">Rp {{ number_format($totalSavings, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sisa Hutang Aktif</p>
            <p class="text-2xl font-bold {{ $totalOutstanding > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                Rp {{ number_format($totalOutstanding, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Savings breakdown --}}
    @if($savingsBalances->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-700">Saldo Simpanan per Produk</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($savingsBalances as $row)
            <div class="px-5 py-3 flex items-center justify-between">
                <span class="text-sm text-gray-700">{{ $row['product']->name }}</span>
                <span class="text-sm font-semibold text-gray-800">Rp {{ number_format($row['balance'], 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Active loans summary --}}
    @if($activeLoans->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-700">Pinjaman Aktif</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($activeLoans as $loan)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $loan->loanProduct->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Cair: {{ $loan->disbursed_date?->format('d/m/Y') }} · Tenor: {{ $loan->tenor_months }} bln</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Sisa</p>
                        <p class="text-sm font-bold text-red-600">Rp {{ number_format($loan->outstanding, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
