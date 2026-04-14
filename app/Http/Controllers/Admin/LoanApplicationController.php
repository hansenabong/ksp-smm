<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationFile;
use App\Models\Member;
use App\Models\LoanProduct;
use App\Services\Loans\LoanService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class LoanApplicationController extends Controller
{
    public function index()
    {
        $applications = LoanApplication::with(['member', 'creator', 'approver', 'loanProduct'])
            ->orderByDesc('trx_date')
            ->paginate(10);

        return view('admin.loans.applications.index', compact('applications'));
    }

    public function create()
    {
        $members = Member::where('is_active', true)->orderBy('member_no')->get();
        $loanProducts = LoanProduct::where('is_active', true)->orderBy('name')->get();
        return view('admin.loans.applications.create', compact('members', 'loanProducts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trx_date' => ['required', 'date'],
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn($q) => $q->where('is_active', true)),
            ],
            'loan_product_id' => [
                'required',
                Rule::exists('loan_products', 'id')->where(fn($q) => $q->where('is_active', true)),
            ],
            'principal' => ['required', 'integer', 'min:1'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:120'],
            'form_data'                              => ['nullable', 'array'],
            // Data Pribadi
            'form_data.nama_lengkap'                 => ['nullable', 'string', 'max:100'],
            'form_data.kelamin'                      => ['nullable', 'in:L,P'],
            'form_data.no_ktp'                       => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir'                    => ['nullable', 'date'],
            'form_data.ktp_berlaku'                  => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup'             => ['nullable', 'boolean'],
            'form_data.status_perkawinan'            => ['nullable', 'in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati'],
            'form_data.pendidikan'                   => ['nullable', 'string', 'max:20'],
            'form_data.nama_ibu_kandung'             => ['nullable', 'string', 'max:100'],
            'form_data.alamat'                       => ['nullable', 'string', 'max:255'],
            'form_data.rt'                           => ['nullable', 'string', 'max:5'],
            'form_data.rw'                           => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kota'                         => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos'                     => ['nullable', 'string', 'max:10'],
            // Alamat KTP
            'form_data.alamat_ktp'                   => ['nullable', 'string', 'max:255'],
            'form_data.rt_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.rw_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kota_ktp'                     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_ktp'                 => ['nullable', 'string', 'max:10'],
            'form_data.telp_rumah'                   => ['nullable', 'string', 'max:20'],
            'form_data.hp'                           => ['nullable', 'string', 'max:20'],
            'form_data.kepemilikan_rumah'            => ['nullable', 'string', 'max:50'],
            'form_data.lama_menempati_thn'           => ['nullable', 'integer', 'min:0'],
            'form_data.lama_menempati_bln'           => ['nullable', 'integer', 'min:0', 'max:11'],
            // Data Pasangan
            'form_data.nama_pasangan'                => ['nullable', 'string', 'max:100'],
            'form_data.jumlah_tanggungan'            => ['nullable', 'integer', 'min:0'],
            'form_data.no_ktp_pasangan'              => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir_pasangan'           => ['nullable', 'date'],
            'form_data.ktp_berlaku_pasangan'         => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup_pasangan'    => ['nullable', 'boolean'],
            // Kontak Darurat
            'form_data.nama_darurat'                 => ['nullable', 'string', 'max:100'],
            'form_data.telp_darurat'                 => ['nullable', 'string', 'max:20'],
            'form_data.alamat_darurat'               => ['nullable', 'string', 'max:255'],
            'form_data.rt_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.rw_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.kode_pos_darurat'             => ['nullable', 'string', 'max:10'],
            'form_data.hubungan_darurat'             => ['nullable', 'string', 'max:50'],
            'form_data.kelurahan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kota_darurat'                 => ['nullable', 'string', 'max:100'],
            // Pekerjaan Pemohon
            'form_data.pekerjaan'                    => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan'                   => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan'            => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan'              => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan'          => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan'              => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan'               => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha'                  => ['nullable', 'string', 'max:100'],
            'form_data.jabatan'                      => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja'                   => ['nullable', 'string', 'max:50'],
            // Pekerjaan Pasangan
            'form_data.pekerjaan_pasangan'           => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan_pasangan'          => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan_pasangan'   => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan_pasangan'     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan_pasangan' => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan_pasangan'     => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan_pasangan'      => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha_pasangan'         => ['nullable', 'string', 'max:100'],
            'form_data.jabatan_pasangan'             => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja_pasangan'          => ['nullable', 'string', 'max:50'],
            // Agunan - Tujuan Pembiayaan
            'form_data.tujuan_pembiayaan'            => ['nullable', 'string', 'max:255'],
            // Agunan - Tanah & Bangunan
            'form_data.lokasi_agunan'                => ['nullable', 'string', 'max:255'],
            'form_data.status_agunan'                => ['nullable', 'in:SHM,SHGB,SHP,HGB,Girik/Letter C,Lainnya'],
            'form_data.atas_nama_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.nilai_agunan'                 => ['nullable', 'integer', 'min:0'],
            'form_data.no_hak'                       => ['nullable', 'string', 'max:50'],
            'form_data.luas_tanah'                   => ['nullable', 'string', 'max:20'],
            'form_data.luas_bangunan'                => ['nullable', 'string', 'max:20'],
            'form_data.no_imb'                       => ['nullable', 'string', 'max:50'],
            // Agunan - Alamat Objek Pembiayaan (Pasal 3 PK)
            'form_data.komplek_agunan'               => ['nullable', 'string', 'max:100'],
            'form_data.jalan_agunan'                 => ['nullable', 'string', 'max:100'],
            'form_data.blok_agunan'                  => ['nullable', 'string', 'max:20'],
            'form_data.no_agunan'                    => ['nullable', 'string', 'max:20'],
            'form_data.rt_agunan'                    => ['nullable', 'string', 'max:5'],
            'form_data.rw_agunan'                    => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.kota_agunan'                  => ['nullable', 'string', 'max:100'],
            'form_data.penerbit_sertifikat'          => ['nullable', 'string', 'max:100'],
            'form_data.tgl_sertifikat'               => ['nullable', 'date'],
            // Agunan - Kendaraan & BPKB
            'form_data.jenis_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.tahun_kendaraan'              => ['nullable', 'integer', 'min:1970', 'max:' . (date('Y') + 1)],
            'form_data.warna_kendaraan'              => ['nullable', 'string', 'max:50'],
            'form_data.no_bpkb'                      => ['nullable', 'string', 'max:50'],
            'form_data.atas_nama_bpkb'               => ['nullable', 'string', 'max:100'],
            'form_data.no_mesin'                     => ['nullable', 'string', 'max:50'],
            'form_data.no_rangka'                    => ['nullable', 'string', 'max:50'],
            'form_data.merk_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.type_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.model_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.bahan_bakar_kendaraan'        => ['nullable', 'string', 'max:50'],
            'form_data.no_faktur_kendaraan'          => ['nullable', 'string', 'max:50'],
            'form_data.no_polisi'                    => ['nullable', 'string', 'max:20'],
            // Pendapatan & Pengeluaran
            'form_data.penghasilan_pemohon'          => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_pasangan'         => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_lain'             => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_sewa'                   => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_hidup'                  => ['nullable', 'integer', 'min:0'],
            'form_data.angsuran_lain'                => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_lain'                   => ['nullable', 'integer', 'min:0'],

            // upload dokumen
            'doc_ktp'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_kk'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_agunan.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_others.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            // tetap ada (boleh override), tapi defaultnya diisi dari produk
            'interest_monthly_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_monthly' => ['nullable', 'integer', 'min:0'],
            'insurance_monthly' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],

            // Notaris (all optional)
            'notaris_name'        => ['nullable', 'string', 'max:150'],
            'notaris_deed_number' => ['nullable', 'string', 'max:60'],
            'notaris_deed_date'   => ['nullable', 'date'],
            'notaris_city'        => ['nullable', 'string', 'max:100'],
        ]);
        $formData = $request->input('form_data', []);
        $formData = $this->normalizeKtpFlags($formData);
        $product = LoanProduct::where('is_active', true)->findOrFail($data['loan_product_id']);

        if ((int)$data['tenor_months'] > (int)$product->max_tenor_months) {
            return back()
                ->withErrors(['tenor_months' => "Tenor melebihi batas produk ({$product->max_tenor_months} bulan)."])
                ->withInput();
        }

        // default dari produk (basis points -> persen)
        $defaultInterestPercent = ($product->interest_rate_monthly_bp ?? 0) / 100.0;

        $interestPercent = array_key_exists('interest_monthly_percent', $data) && $data['interest_monthly_percent'] !== null
            ? (float)$data['interest_monthly_percent']
            : (float)$defaultInterestPercent;

        // Biaya admin diinput manual per pengajuan (tidak lagi diambil dari master jenis pinjaman)
        $adminMonthly = (int)($data['admin_monthly'] ?? 0);

        $insuranceMonthly = (int)($data['insurance_monthly'] ?? 0);

        $app = LoanApplication::create([
            'trx_date' => $data['trx_date'],
            'member_id' => $data['member_id'],

            'principal' => (int)$data['principal'],
            'tenor_months' => (int)$data['tenor_months'],
            'form_data' => $formData,
            // kolom lama yang dipakai LoanService kamu
            'interest_monthly_percent' => $interestPercent,
            'admin_monthly' => $adminMonthly,
            'insurance_monthly' => $insuranceMonthly,

            'notes' => $data['notes'] ?? null,

            // kolom baru snapshot
            'loan_product_id' => $product->id,
            'method' => $product->method,
            'interest_rate_monthly_bp' => (int)$product->interest_rate_monthly_bp,

            'status' => 'PENDING',
            'created_by' => auth()->id(),

            // Notaris
            'notaris_name'        => $data['notaris_name'] ?? null,
            'notaris_deed_number' => $data['notaris_deed_number'] ?? null,
            'notaris_deed_date'   => $data['notaris_deed_date'] ?? null,
            'notaris_city'        => $data['notaris_city'] ?? null,
        ]);
        // upload helper
        $saveOne = function ($file, string $category) use ($app) {
            if (!$file) return;

            $path = $file->store("loan_applications/{$app->id}", 'private');

            LoanApplicationFile::create([
                'loan_application_id' => $app->id,
                'category' => $category,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize() ?? 0,
                'uploaded_by' => auth()->id(),
            ]);
        };

        $saveOne($request->file('doc_ktp'), 'KTP');
        $saveOne($request->file('doc_kk'), 'KK');

        if ($request->hasFile('doc_agunan')) {
            foreach ($request->file('doc_agunan') as $f) {
                $saveOne($f, 'AGUNAN');
            }
        }

        if ($request->hasFile('doc_others')) {
            foreach ($request->file('doc_others') as $f) {
                $saveOne($f, 'LAINNYA');
            }
        }
        return redirect()->route('admin.loans.applications.show', $app)
            ->with('success', 'Pengajuan pinjaman berhasil dibuat. Silakan periksa kembali data di bawah dan cetak review jika perlu.');
    }

    public function edit(LoanApplication $application)
    {
        abort_unless($application->status === 'PENDING', 403, 'Hanya pengajuan PENDING yang bisa diedit.');

        if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
            abort(403);
        }

        $members = Member::where('is_active', true)->orderBy('member_no')->get();
        $loanProducts = LoanProduct::where('is_active', true)->orderBy('name')->get();
        $application->load('files.uploader');

        return view('admin.loans.applications.edit', compact('application', 'members', 'loanProducts'));
    }

    public function update(Request $request, LoanApplication $application)
    {
        abort_unless($application->status === 'PENDING', 403, 'Hanya pengajuan PENDING yang bisa diedit.');

        if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'trx_date' => ['required', 'date'],
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn($q) => $q->where('is_active', true)),
            ],
            'loan_product_id' => [
                'required',
                Rule::exists('loan_products', 'id')->where(fn($q) => $q->where('is_active', true)),
            ],
            'principal' => ['required', 'integer', 'min:1'],
            'tenor_months' => ['required', 'integer', 'min:1', 'max:120'],
            'form_data'                              => ['nullable', 'array'],
            'form_data.nama_lengkap'                 => ['nullable', 'string', 'max:100'],
            'form_data.kelamin'                      => ['nullable', 'in:L,P'],
            'form_data.no_ktp'                       => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir'                    => ['nullable', 'date'],
            'form_data.ktp_berlaku'                  => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup'             => ['nullable', 'boolean'],
            'form_data.status_perkawinan'            => ['nullable', 'in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati'],
            'form_data.pendidikan'                   => ['nullable', 'string', 'max:20'],
            'form_data.nama_ibu_kandung'             => ['nullable', 'string', 'max:100'],
            'form_data.alamat'                       => ['nullable', 'string', 'max:255'],
            'form_data.rt'                           => ['nullable', 'string', 'max:5'],
            'form_data.rw'                           => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kota'                         => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos'                     => ['nullable', 'string', 'max:10'],
            'form_data.alamat_ktp'                   => ['nullable', 'string', 'max:255'],
            'form_data.rt_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.rw_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kota_ktp'                     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_ktp'                 => ['nullable', 'string', 'max:10'],
            'form_data.telp_rumah'                   => ['nullable', 'string', 'max:20'],
            'form_data.hp'                           => ['nullable', 'string', 'max:20'],
            'form_data.kepemilikan_rumah'            => ['nullable', 'string', 'max:50'],
            'form_data.lama_menempati_thn'           => ['nullable', 'integer', 'min:0'],
            'form_data.lama_menempati_bln'           => ['nullable', 'integer', 'min:0', 'max:11'],
            'form_data.nama_pasangan'                => ['nullable', 'string', 'max:100'],
            'form_data.jumlah_tanggungan'            => ['nullable', 'integer', 'min:0'],
            'form_data.no_ktp_pasangan'              => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir_pasangan'           => ['nullable', 'date'],
            'form_data.ktp_berlaku_pasangan'         => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup_pasangan'    => ['nullable', 'boolean'],
            'form_data.nama_darurat'                 => ['nullable', 'string', 'max:100'],
            'form_data.telp_darurat'                 => ['nullable', 'string', 'max:20'],
            'form_data.alamat_darurat'               => ['nullable', 'string', 'max:255'],
            'form_data.rt_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.rw_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.kode_pos_darurat'             => ['nullable', 'string', 'max:10'],
            'form_data.hubungan_darurat'             => ['nullable', 'string', 'max:50'],
            'form_data.kelurahan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kota_darurat'                 => ['nullable', 'string', 'max:100'],
            'form_data.pekerjaan'                    => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan'                   => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan'            => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan'              => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan'          => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan'              => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan'               => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha'                  => ['nullable', 'string', 'max:100'],
            'form_data.jabatan'                      => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja'                   => ['nullable', 'string', 'max:50'],
            'form_data.pekerjaan_pasangan'           => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan_pasangan'          => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan_pasangan'   => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan_pasangan'     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan_pasangan' => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan_pasangan'     => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan_pasangan'      => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha_pasangan'         => ['nullable', 'string', 'max:100'],
            'form_data.jabatan_pasangan'             => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja_pasangan'          => ['nullable', 'string', 'max:50'],
            'form_data.lokasi_agunan'                => ['nullable', 'string', 'max:255'],
            'form_data.status_agunan'                => ['nullable', 'in:SHM,SHGB,SHP,HGB,Girik/Letter C,Lainnya'],
            'form_data.atas_nama_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.nilai_agunan'                 => ['nullable', 'integer', 'min:0'],
            'form_data.no_hak'                       => ['nullable', 'string', 'max:50'],
            'form_data.luas_tanah'                   => ['nullable', 'string', 'max:20'],
            'form_data.luas_bangunan'                => ['nullable', 'string', 'max:20'],
            'form_data.no_imb'                       => ['nullable', 'string', 'max:50'],
            'form_data.jenis_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.tahun_kendaraan'              => ['nullable', 'integer', 'min:1970', 'max:' . (date('Y') + 1)],
            'form_data.warna_kendaraan'              => ['nullable', 'string', 'max:50'],
            'form_data.no_bpkb'                      => ['nullable', 'string', 'max:50'],
            'form_data.atas_nama_bpkb'               => ['nullable', 'string', 'max:100'],
            'form_data.no_mesin'                     => ['nullable', 'string', 'max:50'],
            'form_data.no_rangka'                    => ['nullable', 'string', 'max:50'],
            'form_data.merk_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.type_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.model_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.bahan_bakar_kendaraan'        => ['nullable', 'string', 'max:50'],
            'form_data.no_faktur_kendaraan'          => ['nullable', 'string', 'max:50'],
            'form_data.no_polisi'                    => ['nullable', 'string', 'max:20'],
            'form_data.penghasilan_pemohon'          => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_pasangan'         => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_lain'             => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_sewa'                   => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_hidup'                  => ['nullable', 'integer', 'min:0'],
            'form_data.angsuran_lain'                => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_lain'                   => ['nullable', 'integer', 'min:0'],
            'doc_ktp'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_kk'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_agunan.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_others.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'interest_monthly_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_monthly'            => ['nullable', 'integer', 'min:0'],
            'insurance_monthly'        => ['nullable', 'integer', 'min:0'],
            'notes'                    => ['nullable', 'string', 'max:255'],
            'notaris_name'        => ['nullable', 'string', 'max:150'],
            'notaris_deed_number' => ['nullable', 'string', 'max:60'],
            'notaris_deed_date'   => ['nullable', 'date'],
            'notaris_city'        => ['nullable', 'string', 'max:100'],
        ]);

        $product = LoanProduct::where('is_active', true)->findOrFail($data['loan_product_id']);

        if ((int)$data['tenor_months'] > (int)$product->max_tenor_months) {
            return back()
                ->withErrors(['tenor_months' => "Tenor melebihi batas produk ({$product->max_tenor_months} bulan)."])
                ->withInput();
        }

        $defaultInterestPercent = ($product->interest_rate_monthly_bp ?? 0) / 100.0;

        $interestPercent = array_key_exists('interest_monthly_percent', $data) && $data['interest_monthly_percent'] !== null
            ? (float)$data['interest_monthly_percent']
            : (float)$defaultInterestPercent;

        // Biaya admin diinput manual per pengajuan (tidak lagi diambil dari master jenis pinjaman)
        $adminMonthly = (int)($data['admin_monthly'] ?? 0);

        $insuranceMonthly = (int)($data['insurance_monthly'] ?? 0);

        $application->update([
            'trx_date'                 => $data['trx_date'],
            'member_id'                => $data['member_id'],
            'principal'                => (int)$data['principal'],
            'tenor_months'             => (int)$data['tenor_months'],
            'form_data'                => $this->normalizeKtpFlags($request->input('form_data', [])),
            'interest_monthly_percent' => $interestPercent,
            'admin_monthly'            => $adminMonthly,
            'insurance_monthly'        => $insuranceMonthly,
            'notes'                    => $data['notes'] ?? null,
            'loan_product_id'          => $product->id,
            'method'                   => $product->method,
            'interest_rate_monthly_bp' => (int)$product->interest_rate_monthly_bp,
            'notaris_name'             => $data['notaris_name'] ?? null,
            'notaris_deed_number'      => $data['notaris_deed_number'] ?? null,
            'notaris_deed_date'        => $data['notaris_deed_date'] ?? null,
            'notaris_city'             => $data['notaris_city'] ?? null,
        ]);

        // Upload new files (appended — existing files are NOT replaced)
        $saveOne = function ($file, string $category) use ($application) {
            if (!$file) return;
            $path = $file->store("loan_applications/{$application->id}", 'private');
            LoanApplicationFile::create([
                'loan_application_id' => $application->id,
                'category'            => $category,
                'file_path'           => $path,
                'original_name'       => $file->getClientOriginalName(),
                'mime'                => $file->getClientMimeType(),
                'size'                => $file->getSize() ?? 0,
                'uploaded_by'         => auth()->id(),
            ]);
        };

        $saveOne($request->file('doc_ktp'), 'KTP');
        $saveOne($request->file('doc_kk'), 'KK');

        if ($request->hasFile('doc_agunan')) {
            foreach ($request->file('doc_agunan') as $f) {
                $saveOne($f, 'AGUNAN');
            }
        }

        if ($request->hasFile('doc_others')) {
            foreach ($request->file('doc_others') as $f) {
                $saveOne($f, 'LAINNYA');
            }
        }

        return redirect()->route('admin.loans.applications.show', $application)
            ->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function deleteFile(LoanApplication $application, LoanApplicationFile $file)
    {
        $isSuperAdmin = auth()->user()->hasRole('SUPER_ADMIN');

        // PENDING: SUPER_ADMIN, ADMIN, MANAGER, or PETUGAS (own only)
        // APPROVED: SUPER_ADMIN only (for personal info correction)
        if ($application->status === 'PENDING') {
            if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
                abort(403);
            }
        } elseif ($application->status === 'APPROVED') {
            abort_unless($isSuperAdmin, 403, 'Hanya SUPER_ADMIN yang dapat menghapus file pengajuan yang sudah disetujui.');
        } else {
            abort(403, 'File tidak dapat dihapus pada status pengajuan ini.');
        }

        if ((int)$file->loan_application_id !== (int)$application->id) {
            abort(404);
        }

        Storage::disk('private')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function show(LoanApplication $application)
    {
        // PETUGAS may only view their own applications (IDOR guard)
        if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
            abort(403, 'Anda hanya bisa melihat pengajuan yang Anda buat sendiri.');
        }

        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $application->load(['member', 'loanProduct', 'files.uploader']);
        return view('admin.loans.applications.show', compact('application', 'cashAccounts'));
    }

    /**
     * Edit only personal info (form_data + notaris) on an APPROVED application.
     * Restricted to SUPER_ADMIN — financial terms are immutable after approval.
     */
    public function editPersonalInfo(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 403, 'Hanya pengajuan APPROVED yang bisa diedit data pribadinya.');
        $application->load('files.uploader');
        return view('admin.loans.applications.edit-personal', compact('application'));
    }

    public function updatePersonalInfo(Request $request, LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 403, 'Hanya pengajuan APPROVED yang bisa diedit data pribadinya.');

        $data = $request->validate([
            'form_data'                              => ['nullable', 'array'],
            'form_data.nama_lengkap'                 => ['nullable', 'string', 'max:100'],
            'form_data.kelamin'                      => ['nullable', 'in:L,P'],
            'form_data.no_ktp'                       => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir'                    => ['nullable', 'date'],
            'form_data.ktp_berlaku'                  => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup'             => ['nullable', 'boolean'],
            'form_data.status_perkawinan'            => ['nullable', 'in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati'],
            'form_data.pendidikan'                   => ['nullable', 'string', 'max:20'],
            'form_data.nama_ibu_kandung'             => ['nullable', 'string', 'max:100'],
            'form_data.alamat'                       => ['nullable', 'string', 'max:255'],
            'form_data.rt'                           => ['nullable', 'string', 'max:5'],
            'form_data.rw'                           => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan'                    => ['nullable', 'string', 'max:100'],
            'form_data.kota'                         => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos'                     => ['nullable', 'string', 'max:10'],
            'form_data.alamat_ktp'                   => ['nullable', 'string', 'max:255'],
            'form_data.rt_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.rw_ktp'                       => ['nullable', 'string', 'max:5'],
            'form_data.kelurahan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_ktp'                => ['nullable', 'string', 'max:100'],
            'form_data.kota_ktp'                     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_ktp'                 => ['nullable', 'string', 'max:10'],
            'form_data.telp_rumah'                   => ['nullable', 'string', 'max:20'],
            'form_data.hp'                           => ['nullable', 'string', 'max:20'],
            'form_data.kepemilikan_rumah'            => ['nullable', 'string', 'max:50'],
            'form_data.lama_menempati_thn'           => ['nullable', 'integer', 'min:0'],
            'form_data.lama_menempati_bln'           => ['nullable', 'integer', 'min:0', 'max:11'],
            'form_data.nama_pasangan'                => ['nullable', 'string', 'max:100'],
            'form_data.jumlah_tanggungan'            => ['nullable', 'integer', 'min:0'],
            'form_data.no_ktp_pasangan'              => ['nullable', 'string', 'max:20'],
            'form_data.tgl_lahir_pasangan'           => ['nullable', 'date'],
            'form_data.ktp_berlaku_pasangan'         => ['nullable', 'date'],
            'form_data.ktp_seumur_hidup_pasangan'    => ['nullable', 'boolean'],
            'form_data.nama_darurat'                 => ['nullable', 'string', 'max:100'],
            'form_data.telp_darurat'                 => ['nullable', 'string', 'max:20'],
            'form_data.alamat_darurat'               => ['nullable', 'string', 'max:255'],
            'form_data.rt_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.rw_darurat'                   => ['nullable', 'string', 'max:5'],
            'form_data.kode_pos_darurat'             => ['nullable', 'string', 'max:10'],
            'form_data.hubungan_darurat'             => ['nullable', 'string', 'max:50'],
            'form_data.kelurahan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kecamatan_darurat'            => ['nullable', 'string', 'max:100'],
            'form_data.kota_darurat'                 => ['nullable', 'string', 'max:100'],
            'form_data.pekerjaan'                    => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan'                   => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan'            => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan'              => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan'          => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan'              => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan'               => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha'                  => ['nullable', 'string', 'max:100'],
            'form_data.jabatan'                      => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja'                   => ['nullable', 'string', 'max:50'],
            'form_data.pekerjaan_pasangan'           => ['nullable', 'string', 'max:100'],
            'form_data.perusahaan_pasangan'          => ['nullable', 'string', 'max:200'],
            'form_data.alamat_perusahaan_pasangan'   => ['nullable', 'string', 'max:255'],
            'form_data.kota_perusahaan_pasangan'     => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos_perusahaan_pasangan' => ['nullable', 'string', 'max:10'],
            'form_data.telp_perusahaan_pasangan'     => ['nullable', 'string', 'max:20'],
            'form_data.fax_perusahaan_pasangan'      => ['nullable', 'string', 'max:20'],
            'form_data.jenis_usaha_pasangan'         => ['nullable', 'string', 'max:100'],
            'form_data.jabatan_pasangan'             => ['nullable', 'string', 'max:100'],
            'form_data.masa_kerja_pasangan'          => ['nullable', 'string', 'max:50'],
            'form_data.lokasi_agunan'                => ['nullable', 'string', 'max:255'],
            'form_data.status_agunan'                => ['nullable', 'in:SHM,SHGB,SHP,HGB,Girik/Letter C,Lainnya'],
            'form_data.atas_nama_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.nilai_agunan'                 => ['nullable', 'integer', 'min:0'],
            'form_data.no_hak'                       => ['nullable', 'string', 'max:50'],
            'form_data.luas_tanah'                   => ['nullable', 'string', 'max:20'],
            'form_data.luas_bangunan'                => ['nullable', 'string', 'max:20'],
            'form_data.no_imb'                       => ['nullable', 'string', 'max:50'],
            'form_data.jenis_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.tahun_kendaraan'              => ['nullable', 'integer', 'min:1970', 'max:' . (date('Y') + 1)],
            'form_data.warna_kendaraan'              => ['nullable', 'string', 'max:50'],
            'form_data.no_bpkb'                      => ['nullable', 'string', 'max:50'],
            'form_data.atas_nama_bpkb'               => ['nullable', 'string', 'max:100'],
            'form_data.no_mesin'                     => ['nullable', 'string', 'max:50'],
            'form_data.no_rangka'                    => ['nullable', 'string', 'max:50'],
            'form_data.merk_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.type_kendaraan'               => ['nullable', 'string', 'max:100'],
            'form_data.model_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.bahan_bakar_kendaraan'        => ['nullable', 'string', 'max:50'],
            'form_data.no_faktur_kendaraan'          => ['nullable', 'string', 'max:50'],
            'form_data.no_polisi'                    => ['nullable', 'string', 'max:20'],
            'form_data.penghasilan_pemohon'          => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_pasangan'         => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_lain'             => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_sewa'                   => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_hidup'                  => ['nullable', 'integer', 'min:0'],
            'form_data.angsuran_lain'                => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_lain'                   => ['nullable', 'integer', 'min:0'],
            // Notaris
            'notaris_name'        => ['nullable', 'string', 'max:150'],
            'notaris_deed_number' => ['nullable', 'string', 'max:60'],
            'notaris_deed_date'   => ['nullable', 'date'],
            'notaris_city'        => ['nullable', 'string', 'max:100'],
            // Additional files
            'doc_ktp'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_kk'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_agunan.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'doc_others.*'  => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
        ]);

        // Only update personal/notaris fields — financial terms are immutable
        $application->update([
            'form_data'           => $this->normalizeKtpFlags($request->input('form_data', $application->form_data ?? [])),
            'notaris_name'        => $data['notaris_name'] ?? null,
            'notaris_deed_number' => $data['notaris_deed_number'] ?? null,
            'notaris_deed_date'   => $data['notaris_deed_date'] ?? null,
            'notaris_city'        => $data['notaris_city'] ?? null,
        ]);

        // Append new files
        $saveOne = function ($file, string $category) use ($application) {
            if (!$file) return;
            $path = $file->store("loan_applications/{$application->id}", 'private');
            LoanApplicationFile::create([
                'loan_application_id' => $application->id,
                'category'            => $category,
                'file_path'           => $path,
                'original_name'       => $file->getClientOriginalName(),
                'mime'                => $file->getClientMimeType(),
                'size'                => $file->getSize() ?? 0,
                'uploaded_by'         => auth()->id(),
            ]);
        };

        $saveOne($request->file('doc_ktp'), 'KTP');
        $saveOne($request->file('doc_kk'), 'KK');

        if ($request->hasFile('doc_agunan')) {
            foreach ($request->file('doc_agunan') as $f) {
                $saveOne($f, 'AGUNAN');
            }
        }

        if ($request->hasFile('doc_others')) {
            foreach ($request->file('doc_others') as $f) {
                $saveOne($f, 'LAINNYA');
            }
        }

        return redirect()->route('admin.loans.applications.show', $application)
            ->with('success', 'Data pribadi pengajuan berhasil diperbarui.');
    }

    public function financeReview(Request $request, LoanApplication $application, AuditLogService $audit)
    {
        abort_unless($application->status === 'PENDING', 403, 'Hanya pengajuan PENDING yang bisa di-review Finance.');
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'FINANCE']), 403);

        $data = $request->validate([
            'method'                   => ['required', 'in:FLAT,EFEKTIF'],
            'interest_monthly_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'finance_note'             => ['nullable', 'string', 'max:255'],
        ]);

        $application->update([
            'method'                   => $data['method'],
            'interest_monthly_percent' => (float) $data['interest_monthly_percent'],
            'status'                   => 'FINANCE_REVIEWED',
            'approval_note'            => $data['finance_note'] ?? $application->approval_note,
        ]);

        $audit->record('finance_review', 'LoanApplication', $application->id,
            "Finance review pengajuan #{$application->id}: {$data['method']} {$data['interest_monthly_percent']}%/bln",
            ['method' => $data['method'], 'rate' => $data['interest_monthly_percent']]
        );

        return redirect()->route('admin.loans.applications.show', $application)
            ->with('success', 'Review Finance berhasil. Bunga ' . $data['method'] . ' ' . $data['interest_monthly_percent'] . '%/bln telah di-set.');
    }

    public function approve(Request $request, LoanApplication $application, LoanService $service, AuditLogService $audit)
    {
        $validated = $request->validate([
            'trx_date'             => ['required', 'date'],
            'from_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'approval_note'        => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $service->approveAndDisburse($application->id, $validated, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $audit->record('approve', 'LoanApplication', $application->id,
            "Pengajuan pinjaman #{$application->id} [{$application->member?->member_no}] disetujui & dicairkan",
            ['amount' => (int) $application->amount, 'note' => $validated['approval_note'] ?? null]
        );

        return redirect()->route('admin.loans.applications.index')->with('success', 'Pengajuan disetujui & pinjaman dicairkan.');
    }

    public function reject(Request $request, LoanApplication $application, LoanService $service, AuditLogService $audit)
    {
        $validated = $request->validate([
            'approval_note' => ['required', 'string', 'max:255'],
        ]);

        $service->reject($application->id, $validated['approval_note'], auth()->id());

        $audit->record('reject', 'LoanApplication', $application->id,
            "Pengajuan pinjaman #{$application->id} [{$application->member?->member_no}] ditolak",
            ['amount' => (int) $application->amount, 'note' => $validated['approval_note']]
        );

        return redirect()->route('admin.loans.applications.index')->with('success', 'Pengajuan ditolak.');
    }
    /**
     * Cast e-KTP "seumur hidup" checkboxes to clean booleans.
     * When seumur hidup is on, the corresponding ktp_berlaku date is wiped
     * so the JSON has a single source of truth.
     */
    private function normalizeKtpFlags(array $formData): array
    {
        $seumur = !empty($formData['ktp_seumur_hidup']);
        $formData['ktp_seumur_hidup'] = $seumur;
        if ($seumur) {
            $formData['ktp_berlaku'] = null;
        }

        $seumurPas = !empty($formData['ktp_seumur_hidup_pasangan']);
        $formData['ktp_seumur_hidup_pasangan'] = $seumurPas;
        if ($seumurPas) {
            $formData['ktp_berlaku_pasangan'] = null;
        }

        return $formData;
    }

    public function reviewPdf(LoanApplication $application)
    {
        // ADMIN_DATA only sees their own; others (SUPER_ADMIN, TREASURY, PENGAWAS) see all
        if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
            abort(403);
        }

        $application->load(['member', 'loanProduct', 'creator', 'approver', 'files']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.loans.applications.review-pdf',
            compact('application')
        );
        $pdf->setPaper('A4', 'portrait');

        $filename = 'review-pengajuan-' . str_pad($application->id, 5, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->stream($filename);
    }

    public function penawaranPembiayaan(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 404);

        $application->load(['member', 'loanProduct', 'approver', 'collaterals']);

        $loan = \App\Models\Loan::where('loan_application_id', $application->id)->first();

        $nomorSurat = 'SPP-' . now()->format('Y') . '/' . str_pad($application->id, 4, '0', STR_PAD_LEFT);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.loans.applications.penawaran-pembiayaan-pdf',
            compact('application', 'loan', 'nomorSurat')
        );
        $pdf->setPaper('A4', 'portrait');

        $filename = 'penawaran-pembiayaan-' . str_replace('/', '-', $nomorSurat) . '.pdf';

        return $pdf->download($filename);
    }

    public function agreement(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 404);

        $application->load(['member', 'loanProduct', 'approver']);

        $loan = \App\Models\Loan::where('loan_application_id', $application->id)->first();

        $nomorPk = 'PK-' . $application->trx_date->format('Y') . '/' . str_pad($application->id, 4, '0', STR_PAD_LEFT);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.loans.applications.agreement-pdf', compact('application', 'loan', 'nomorPk'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'perjanjian-' . str_replace('/', '-', $nomorPk) . '.pdf';

        return $pdf->download($filename);
    }

    public function agreementVehicle(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 404);

        $application->load(['member', 'loanProduct', 'approver']);

        $loan = \App\Models\Loan::where('loan_application_id', $application->id)->first();

        $nomorPk = 'PK-' . $application->trx_date->format('Y') . '/' . str_pad($application->id, 4, '0', STR_PAD_LEFT);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.loans.applications.agreement-vehicle-pdf', compact('application', 'loan', 'nomorPk'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'perjanjian-kendaraan-' . str_replace('/', '-', $nomorPk) . '.pdf';

        return $pdf->download($filename);
    }

    public function agreementWord(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 404);

        $application->load(['member', 'loanProduct', 'approver']);
        $loan = \App\Models\Loan::where('loan_application_id', $application->id)->first();
        $nomorPk = 'PK-' . $application->trx_date->format('Y') . '/' . str_pad($application->id, 4, '0', STR_PAD_LEFT);

        $html = view('admin.loans.applications.agreement-pdf', compact('application', 'loan', 'nomorPk'))->render();
        $filename = 'perjanjian-' . str_replace('/', '-', $nomorPk) . '.docx';

        return $this->buildWordFromHtml($html, $filename);
    }

    public function agreementVehicleWord(LoanApplication $application)
    {
        abort_unless($application->status === 'APPROVED', 404);

        $application->load(['member', 'loanProduct', 'approver']);
        $loan = \App\Models\Loan::where('loan_application_id', $application->id)->first();
        $nomorPk = 'PK-' . $application->trx_date->format('Y') . '/' . str_pad($application->id, 4, '0', STR_PAD_LEFT);

        $html = view('admin.loans.applications.agreement-vehicle-pdf', compact('application', 'loan', 'nomorPk'))->render();
        $filename = 'perjanjian-kendaraan-' . str_replace('/', '-', $nomorPk) . '.docx';

        return $this->buildWordFromHtml($html, $filename);
    }

    /**
     * Convert rendered Blade HTML into a Word .docx download.
     * PhpWord's Html parser expects body-only XHTML fragments — strip
     * the full-document wrapper and convert HTML entities to UTF-8.
     */
    private function buildWordFromHtml(string $rawHtml, string $filename)
    {
        // 1. Inline CSS classes → inline styles (before stripping <style>)
        $cssInliner = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
        $rawHtml = $cssInliner->convert($rawHtml);

        // 2. Extract only the <body> content
        $body = $rawHtml;
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $rawHtml, $m)) {
            $body = $m[1];
        }

        // 3. Strip <style> blocks (no longer needed after inlining)
        $body = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $body);

        // 4. Remove <img> tags (not supported well in PhpWord HTML parser)
        $body = preg_replace('/<img[^>]*\/?>/i', '', $body);

        // 5. Convert float-based numbered layouts to inline text
        //    e.g. <div style="float:left;...">1.</div><div style="margin-left:...">text</div>
        //    → <span>1. </span>  (PhpWord doesn't support CSS float)
        $body = preg_replace('/<div[^>]*float\s*:\s*left[^>]*>(.*?)<\/div>\s*/is', '<span>$1 </span>', $body);

        // 6. Apply text-transform:uppercase — PhpWord ignores this CSS property
        $body = preg_replace_callback(
            '/<([a-z][a-z0-9]*)\b([^>]*text-transform\s*:\s*uppercase[^>]*)>(.*?)<\/\1>/is',
            fn($m) => '<' . $m[1] . $m[2] . '>' . mb_strtoupper($m[3]) . '</' . $m[1] . '>',
            $body
        );

        // 7. Convert self-closing void tags to XHTML format for XML parser
        $body = preg_replace('/<(br|hr|input)\b([^>]*?)\/?\s*>/i', '<$1$2 />', $body);

        // 8. Decode HTML entities to UTF-8 characters (PhpWord needs XML-safe text)
        $body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 9. Re-encode stray ampersands that aren't part of valid XML entities
        $body = preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;|#\d+;|#x[0-9a-fA-F]+;)/', '&amp;', $body);

        // 10. Strip HTML comments
        $body = preg_replace('/<!--.*?-->/s', '', $body);

        // 11. Wrap in a minimal div so PhpWord gets a single root element
        $body = '<div>' . trim($body) . '</div>';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'pageSizeW'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(21),
            'pageSizeH'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(29.7),
            'marginTop'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginBottom'=> \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginLeft'  => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
        ]);

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $body, false, false);

        $temp = storage_path('app/temp/' . $filename);

        if (!is_dir(dirname($temp))) {
            mkdir(dirname($temp), 0755, true);
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function downloadFile(\App\Models\LoanApplication $application, \App\Models\LoanApplicationFile $file)
    {
        // Verify file belongs to this application
        if ((int)$file->loan_application_id !== (int)$application->id) {
            abort(404);
        }

        // PETUGAS may only download files from their own applications (IDOR guard)
        if (auth()->user()->hasRole('ADMIN_DATA') && (int)$application->created_by !== auth()->id()) {
            abort(403, 'Anda hanya bisa mengakses dokumen pengajuan yang Anda buat sendiri.');
        }

        return Storage::disk('private')->download($file->file_path, $file->original_name);
    }
}
