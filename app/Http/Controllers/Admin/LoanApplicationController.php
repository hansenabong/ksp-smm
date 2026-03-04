<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationFile;
use App\Models\Member;
use App\Models\LoanProduct;
use App\Services\Loans\LoanService;
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
            'form_data.status_perkawinan'            => ['nullable', 'in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati'],
            'form_data.pendidikan'                   => ['nullable', 'string', 'max:20'],
            'form_data.nama_ibu_kandung'             => ['nullable', 'string', 'max:100'],
            'form_data.alamat'                       => ['nullable', 'string', 'max:255'],
            'form_data.rt'                           => ['nullable', 'string', 'max:5'],
            'form_data.rw'                           => ['nullable', 'string', 'max:5'],
            'form_data.kota'                         => ['nullable', 'string', 'max:100'],
            'form_data.kode_pos'                     => ['nullable', 'string', 'max:10'],
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
            // Agunan - Tanah & Bangunan
            'form_data.lokasi_agunan'                => ['nullable', 'string', 'max:255'],
            'form_data.status_agunan'                => ['nullable', 'in:SHM,SHGB,SHP,HGB,Girik/Letter C,Lainnya'],
            'form_data.atas_nama_agunan'             => ['nullable', 'string', 'max:100'],
            'form_data.nilai_agunan'                 => ['nullable', 'integer', 'min:0'],
            'form_data.no_hak'                       => ['nullable', 'string', 'max:50'],
            'form_data.luas_tanah'                   => ['nullable', 'string', 'max:20'],
            'form_data.luas_bangunan'                => ['nullable', 'string', 'max:20'],
            'form_data.no_imb'                       => ['nullable', 'string', 'max:50'],
            // Agunan - Kendaraan & BPKB
            'form_data.jenis_kendaraan'              => ['nullable', 'string', 'max:100'],
            'form_data.tahun_kendaraan'              => ['nullable', 'integer', 'min:1970', 'max:' . (date('Y') + 1)],
            'form_data.warna_kendaraan'              => ['nullable', 'string', 'max:50'],
            'form_data.no_bpkb'                      => ['nullable', 'string', 'max:50'],
            'form_data.atas_nama_bpkb'               => ['nullable', 'string', 'max:100'],
            'form_data.no_mesin'                     => ['nullable', 'string', 'max:50'],
            'form_data.no_rangka'                    => ['nullable', 'string', 'max:50'],
            // Pendapatan & Pengeluaran
            'form_data.penghasilan_pemohon'          => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_pasangan'         => ['nullable', 'integer', 'min:0'],
            'form_data.penghasilan_lain'             => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_sewa'                   => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_hidup'                  => ['nullable', 'integer', 'min:0'],
            'form_data.angsuran_lain'                => ['nullable', 'integer', 'min:0'],
            'form_data.biaya_lain'                   => ['nullable', 'integer', 'min:0'],

            // upload dokumen fixed (single)
            'doc_ktp' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'doc_kk' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'doc_agunan' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // tambahan dokumen (multiple)
            'doc_others.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            // tetap ada (boleh override), tapi defaultnya diisi dari produk
            'interest_monthly_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_monthly' => ['nullable', 'integer', 'min:0'],
            'insurance_monthly' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],

        ]);
        $formData = $request->input('form_data', []);
        $product = LoanProduct::where('is_active', true)->findOrFail($data['loan_product_id']);

        if ((int)$data['tenor_months'] > (int)$product->max_tenor_months) {
            return back()
                ->withErrors(['tenor_months' => "Tenor melebihi batas produk ({$product->max_tenor_months} bulan)."])
                ->withInput();
        }

        // default dari produk (basis points -> persen)
        $defaultInterestPercent = ($product->interest_rate_monthly_bp ?? 0) / 100.0;

        // NOTE: admin_fee di product kita treat sebagai admin per bulan (biar kompatibel dengan loan logic kamu sekarang)
        $defaultAdminMonthly = (int)($product->admin_fee ?? 0);

        $interestPercent = array_key_exists('interest_monthly_percent', $data) && $data['interest_monthly_percent'] !== null
            ? (float)$data['interest_monthly_percent']
            : (float)$defaultInterestPercent;

        $adminMonthly = array_key_exists('admin_monthly', $data) && $data['admin_monthly'] !== null
            ? (int)$data['admin_monthly']
            : (int)$defaultAdminMonthly;

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
            'admin_fee' => (int)$product->admin_fee,

            'status' => 'PENDING',
            'created_by' => auth()->id(),
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
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize() ?? 0,
                'uploaded_by' => auth()->id(),
            ]);
        };

        $saveOne($request->file('doc_ktp'), 'KTP');
        $saveOne($request->file('doc_kk'), 'KK');
        $saveOne($request->file('doc_agunan'), 'AGUNAN');

        if ($request->hasFile('doc_others')) {
            foreach ($request->file('doc_others') as $f) {
                $saveOne($f, 'LAINNYA');
            }
        }
        return redirect()->route('admin.loans.applications.index')
            ->with('success', 'Pengajuan pinjaman berhasil dibuat.');
    }

    public function show(LoanApplication $application)
    {
        $cashAccounts = CashAccount::where('is_active', true)->orderBy('name')->get();
        $application->load(['member', 'loanProduct', 'files.uploader']);
        return view('admin.loans.applications.show', compact('application', 'cashAccounts'));
    }

    public function approve(Request $request, LoanApplication $application, LoanService $service)
    {
        // hanya role tertentu (optional hardening)
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'trx_date' => ['required', 'date'],
            'from_cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'approval_note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $service->approveAndDisburse($application->id, $validated, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('admin.loans.applications.index')->with('success', 'Pengajuan disetujui & pinjaman dicairkan.');
    }

    public function reject(Request $request, LoanApplication $application, LoanService $service)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'BENDAHARA', 'MANAGER']), 403);

        $validated = $request->validate([
            'approval_note' => ['required', 'string', 'max:255'],
        ]);

        $service->reject($application->id, $validated['approval_note'], auth()->id());

        return redirect()->route('admin.loans.applications.index')->with('success', 'Pengajuan ditolak.');
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

    public function downloadFile(\App\Models\LoanApplication $application, \App\Models\LoanApplicationFile $file)
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER', 'PETUGAS']), 403);

        // pastikan file milik application ini
        if ((int)$file->loan_application_id !== (int)$application->id) {
            abort(404);
        }

        return Storage::disk('private')->download($file->file_path, $file->original_name);
    }
}
