<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Periode SHU</h2>
            <a href="{{ route('admin.shu.index') }}"
               class="text-sm text-gray-500 hover:text-gray-800 border border-gray-200 rounded-lg px-3 py-1.5 transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl mx-auto">

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.shu.store') }}" method="POST"
              x-data="{
                cadanganUmum: {{ old('cadangan_umum_bp', $defaults['cadangan_umum_bp']) }},
                jasaSimpanan: {{ old('jasa_simpanan_bp', $defaults['jasa_simpanan_bp']) }},
                jasaUsaha:    {{ old('jasa_usaha_bp',    $defaults['jasa_usaha_bp']) }},
                pengurus:     {{ old('pengurus_bp',      $defaults['pengurus_bp']) }},
                pegawai:      {{ old('pegawai_bp',       $defaults['pegawai_bp']) }},
                sosial:       {{ old('sosial_bp',        $defaults['sosial_bp']) }},
                pendidikan:   {{ old('pendidikan_bp',    $defaults['pendidikan_bp']) }},
                get total() {
                    return this.cadanganUmum + this.jasaSimpanan + this.jasaUsaha
                         + this.pengurus + this.pegawai + this.sosial + this.pendidikan;
                },
                fmt(bp) { return (bp / 100).toFixed(2) + '%'; }
              }">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <span class="font-semibold text-gray-700">Informasi Periode</span>
                    <span class="text-xs text-gray-400">* Wajib diisi</span>
                </div>
                <div class="px-5 py-4 space-y-4">

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                            Tahun <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="year" value="{{ old('year', $suggestedYear) }}"
                               min="2000" max="2099" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                        @error('year')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Dari Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_from" value="{{ old('date_from', $suggestedYear . '-01-01') }}" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            @error('date_from')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                                Sampai Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="date_to" value="{{ old('date_to', $suggestedYear . '-12-31') }}" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">
                            @error('date_to')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Alokasi SHU</span>
                        <span class="text-xs font-mono"
                              :class="total === 10000 ? 'text-green-600' : 'text-red-600'">
                            Total: <span x-text="fmt(total)"></span>
                            <span x-show="total !== 10000"> ⚠ harus 100%</span>
                            <span x-show="total === 10000"> ✓</span>
                        </span>
                    </div>

                    @error('total_bp')
                    <div class="text-red-600 text-xs mb-3 bg-red-50 rounded p-2">{{ $message }}</div>
                    @enderror

                    @php
                    $fields = [
                        ['cadanganUmum', 'cadangan_umum_bp', 'Dana Cadangan Umum'],
                        ['jasaSimpanan', 'jasa_simpanan_bp', 'Jasa Simpanan (anggota)'],
                        ['jasaUsaha',    'jasa_usaha_bp',    'Jasa Usaha (anggota)'],
                        ['pengurus',     'pengurus_bp',      'Dana Pengurus'],
                        ['pegawai',      'pegawai_bp',       'Dana Pegawai'],
                        ['sosial',       'sosial_bp',        'Dana Sosial'],
                        ['pendidikan',   'pendidikan_bp',    'Dana Pendidikan'],
                    ];
                    @endphp

                    <div class="space-y-3">
                        @foreach($fields as [$xvar, $name, $label])
                        <div class="flex items-center gap-3">
                            <label class="w-52 text-sm text-gray-700 shrink-0">{{ $label }}</label>
                            <input type="number" name="{{ $name }}" min="0" max="10000" step="1"
                                   x-model.number="{{ $xvar }}"
                                   class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right font-mono focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <span class="text-xs text-gray-400 font-mono" x-text="fmt({{ $xvar }})"></span>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Satuan: basis point (bp). 10 000 bp = 100%.</p>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <a href="{{ route('admin.shu.index') }}"
                   class="text-sm text-gray-600 border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        :disabled="total !== 10000"
                        :class="total !== 10000 ? 'opacity-50 cursor-not-allowed' : ''"
                        class="bg-gray-800 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-gray-700 transition">
                    Hitung &amp; Simpan
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
