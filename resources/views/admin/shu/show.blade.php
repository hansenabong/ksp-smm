<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">SHU Tahun {{ $shuPeriod->year }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Periode {{ $shuPeriod->date_from->format('d/m/Y') }} – {{ $shuPeriod->date_to->format('d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($shuPeriod->isFinalized())
                <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">FINALIZED</span>
                @else
                <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">DRAFT</span>
                @endif
                <a href="{{ route('admin.shu.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg px-3 py-1.5 transition">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Summary Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-700">Ringkasan Distribusi SHU</div>
            <div class="px-5 py-4">
                <div class="text-center mb-6">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">SHU Bruto</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">Rp {{ number_format($shuPeriod->shu_bruto, 0, ',', '.') }}</p>
                </div>

                @php
                $buckets = [
                    ['Dana Cadangan Umum',      $shuPeriod->cadangan_umum_bp, $shuPeriod->cadangan_umum_amount],
                    ['Jasa Simpanan (Anggota)', $shuPeriod->jasa_simpanan_bp, $shuPeriod->jasa_simpanan_amount],
                    ['Jasa Usaha (Anggota)',    $shuPeriod->jasa_usaha_bp,    $shuPeriod->jasa_usaha_amount],
                    ['Dana Pengurus',           $shuPeriod->pengurus_bp,      $shuPeriod->pengurus_amount],
                    ['Dana Pegawai',            $shuPeriod->pegawai_bp,       $shuPeriod->pegawai_amount],
                    ['Dana Sosial',             $shuPeriod->sosial_bp,        $shuPeriod->sosial_amount],
                    ['Dana Pendidikan',         $shuPeriod->pendidikan_bp,    $shuPeriod->pendidikan_amount],
                ];
                @endphp

                <table class="w-full text-sm">
                    <thead class="border-b border-gray-100">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="pb-2 text-left">Pos</th>
                            <th class="pb-2 text-right">%</th>
                            <th class="pb-2 text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($buckets as [$label, $bp, $amount])
                        <tr>
                            <td class="py-2 text-gray-700">{{ $label }}</td>
                            <td class="py-2 text-right text-gray-500 font-mono">{{ number_format($bp / 100, 2) }}%</td>
                            <td class="py-2 text-right font-mono font-semibold text-gray-800">{{ number_format($amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200">
                        <tr>
                            <td class="pt-2 text-gray-700 font-bold">Total</td>
                            <td class="pt-2 text-right font-mono font-bold text-gray-800">100.00%</td>
                            <td class="pt-2 text-right font-mono font-bold text-gray-800">
                                {{ number_format(collect($buckets)->sum(fn($b) => $b[2]), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Actions --}}
            @if($shuPeriod->isDraft())
            <div class="px-5 py-4 border-t border-gray-100 flex items-center gap-3 bg-gray-50">
                <form action="{{ route('admin.shu.recalculate', $shuPeriod) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="text-sm text-gray-700 border border-gray-300 rounded-lg px-4 py-2 hover:bg-white transition">
                        Hitung Ulang
                    </button>
                </form>

                @if($shuPeriod->shu_bruto > 0)
                <form action="{{ route('admin.shu.finalize', $shuPeriod) }}" method="POST"
                      onsubmit="return confirm('Finalisasi SHU {{ $shuPeriod->year }}? Tindakan ini akan membuat jurnal dan tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit"
                            class="text-sm bg-green-700 text-white font-semibold rounded-lg px-5 py-2 hover:bg-green-600 transition">
                        Finalisasi &amp; Buat Jurnal
                    </button>
                </form>
                @endif

                @if(auth()->user()->hasAnyRole(['SUPER_ADMIN','ADMIN']))
                <form action="{{ route('admin.shu.destroy', $shuPeriod) }}" method="POST" class="ml-auto"
                      onsubmit="return confirm('Hapus periode SHU {{ $shuPeriod->year }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-sm text-red-600 border border-red-200 rounded-lg px-4 py-2 hover:bg-red-50 transition">
                        Hapus
                    </button>
                </form>
                @endif
            </div>
            @else
            <div class="px-5 py-3 border-t border-gray-100 bg-green-50 text-xs text-green-700">
                Difinalisasi oleh {{ $shuPeriod->finalizer?->name ?? '-' }}
                pada {{ $shuPeriod->finalized_at?->format('d/m/Y H:i') ?? '-' }}.
                @if($shuPeriod->journalEntry)
                Jurnal:
                <a href="{{ route('admin.accounting.journal_entries.show', $shuPeriod->journalEntry) }}"
                   class="underline">{{ $shuPeriod->journalEntry->id }}</a>
                @endif
            </div>
            @endif
        </div>

        {{-- Member Allocations --}}
        @if($allocations->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <span class="font-semibold text-gray-700">Alokasi Per Anggota</span>
                <span class="text-xs text-gray-400">{{ $allocations->count() }} anggota</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">No.</th>
                            <th class="px-4 py-3 text-left">Anggota</th>
                            <th class="px-4 py-3 text-right">Rata-rata Simpanan</th>
                            <th class="px-4 py-3 text-right">Total Angsuran</th>
                            <th class="px-4 py-3 text-right">SHU Simpanan</th>
                            <th class="px-4 py-3 text-right">SHU Usaha</th>
                            <th class="px-4 py-3 text-right">SHU Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($allocations as $i => $alloc)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-800">{{ $alloc->member->name }}</p>
                                <p class="text-xs text-gray-400">{{ $alloc->member->member_no }}</p>
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-600 text-xs">
                                {{ number_format($alloc->avg_savings, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-600 text-xs">
                                {{ number_format($alloc->total_loan_payments, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                                {{ number_format($alloc->shu_simpanan, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                                {{ number_format($alloc->shu_usaha, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-gray-800">
                                {{ number_format($alloc->shu_total, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr class="text-xs font-bold text-gray-700">
                            <td colspan="4" class="px-4 py-3">Total</td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ number_format($allocations->sum('shu_simpanan'), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ number_format($allocations->sum('shu_usaha'), 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ number_format($allocations->sum('shu_total'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

    </div>
</x-admin-layout>
