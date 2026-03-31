<x-admin-layout>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">Panduan Pengguna Sistem KSP</h2>
            <p class="text-sm text-gray-500 mt-1">Pilih panduan sesuai role Anda. Klik untuk membuka, lalu cetak atau simpan sebagai PDF.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $guides = [
                    ['role' => 'super_admin', 'label' => 'Super Admin', 'desc' => 'Akses penuh: persetujuan pinjaman, manajemen user, VOID, dan semua fitur sistem.', 'color' => 'rose'],
                    ['role' => 'finance', 'label' => 'Finance', 'desc' => 'Kas, simpanan, laporan keuangan, akuntansi, audit, dan master data keuangan.', 'color' => 'emerald'],
                    ['role' => 'admin_data', 'label' => 'Admin Data', 'desc' => 'Pengajuan pinjaman, master anggota, dan data agunan.', 'color' => 'blue'],
                    ['role' => 'admin_collection', 'label' => 'Admin Collection', 'desc' => 'Penagihan, pembayaran angsuran, surat penagihan, dan laporan kolektibilitas.', 'color' => 'amber'],
                    ['role' => 'treasury', 'label' => 'Treasury', 'desc' => 'Cetak dokumen PK, kwitansi, permohonan dana, dan jadwal angsuran.', 'color' => 'purple'],
                    ['role' => 'pengawas', 'label' => 'Pengawas', 'desc' => 'Monitoring read-only: pengajuan, pinjaman, collection, dan anggota.', 'color' => 'cyan'],
                    ['role' => 'legal', 'label' => 'Legal', 'desc' => 'Dokumen legal, penagihan, dan monitoring pinjaman.', 'color' => 'slate'],
                ];
            @endphp

            @foreach($guides as $g)
                <a href="{{ route('admin.guide.show', $g['role']) }}" target="_blank"
                   class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md hover:border-{{ $g['color'] }}-300 transition-all duration-200">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-{{ $g['color'] }}-100 text-{{ $g['color'] }}-700">
                            {{ $g['label'] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $g['desc'] }}</p>
                    <div class="mt-3 text-xs font-semibold text-{{ $g['color'] }}-600 group-hover:underline">
                        Buka Panduan &rarr;
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
            <strong>Tips:</strong> Setelah membuka panduan, klik tombol "Cetak / Simpan PDF" atau tekan <kbd class="px-1.5 py-0.5 bg-blue-200 rounded text-xs">Ctrl+P</kbd> untuk menyimpan sebagai file PDF.
        </div>
    </div>
</x-admin-layout>
