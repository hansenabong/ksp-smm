<?php

namespace App\Services\Document;

use App\Models\Loan;
use App\Models\LoanApplication;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;

class PkWordService
{
    // ─── Font presets ────────────────────────────────────────────────────────
    private array $fNormal  = ['name' => 'Times New Roman', 'size' => 11];
    private array $fBold    = ['name' => 'Times New Roman', 'size' => 11, 'bold' => true];
    private array $fTitle   = ['name' => 'Times New Roman', 'size' => 14, 'bold' => true];
    private array $fPasal   = ['name' => 'Times New Roman', 'size' => 11, 'bold' => true];
    private array $fUl      = ['name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'underline' => 'single'];

    // ─── Paragraph presets ───────────────────────────────────────────────────
    private array $pCenter  = ['alignment' => 'center', 'spaceAfter' => 0];
    private array $pJustify = ['alignment' => 'both',   'spaceAfter' => 80, 'lineHeight' => 1.5];
    private array $pIndent  = ['alignment' => 'both',   'spaceAfter' => 60, 'lineHeight' => 1.5,
                               'indentation' => ['left' => 360, 'hanging' => 360]];

    // ─── Section style (populated in constructor) ─────────────────────────────
    private array $sectionStyle = [];

    public function __construct()
    {
        $this->sectionStyle = [
            'pageSizeW'   => Converter::cmToTwip(21),
            'pageSizeH'   => Converter::cmToTwip(29.7),
            'marginTop'   => Converter::cmToTwip(2),
            'marginBottom'=> Converter::cmToTwip(2),
            'marginLeft'  => Converter::cmToTwip(2.5),
            'marginRight' => Converter::cmToTwip(2.5),
        ];
    }

    // =========================================================================
    //  PUBLIC
    // =========================================================================

    public function buildSertipikat(LoanApplication $app, ?Loan $loan, string $nomorPk): string
    {
        [$phpWord, $section, $vars] = $this->bootstrap($app, $loan, $nomorPk);

        $fd = array_merge($app->form_data ?? [], $app->doc_overrides ?? []);
        $agunan = [
            'no_hak'    => $fd['no_hak']          ?? 'XXXX',
            'kelurahan' => $fd['kelurahan_agunan'] ?? 'Kelurahan',
            'tgl_surat' => $fd['tgl_sertifikat']  ?? 'XX-XX-XXXX',
            'nib'       => $fd['nib_agunan']       ?? 'XX.xx.xx.xx',
            'nop'       => $fd['nop_agunan']       ?? 'XX.XX.XXX.XXX.XXX.XXX.X',
            'provinsi'  => $fd['provinsi_agunan']  ?? '........................',
            'kab'       => $fd['kota_agunan']      ?? '............................',
            'kec'       => $fd['kecamatan_agunan'] ?? '............................',
            'kel'       => $fd['kelurahan_agunan'] ?? '....................',
            'jalan'     => $fd['jalan_agunan']     ?? '..........................',
            'atas_nama' => $fd['atas_nama_agunan'] ?? '.........................................',
        ];

        $this->buildCommonStart($section, $vars);
        $this->addPasal4Sertipikat($section, $agunan);
        $this->addPasal5Sertipikat($section);
        $this->buildCommonEnd($section, $vars);

        return $this->save($phpWord, 'pk-sertipikat-');
    }

    public function buildKendaraan(LoanApplication $app, ?Loan $loan, string $nomorPk): string
    {
        [$phpWord, $section, $vars] = $this->bootstrap($app, $loan, $nomorPk);

        $fd = array_merge($app->form_data ?? [], $app->doc_overrides ?? []);
        $kendaraan = [
            'merk'     => trim(($fd['merk_kendaraan'] ?? '') . ' ' . ($fd['type_kendaraan'] ?? '')) ?: '___',
            'tahun'    => $fd['tahun_kendaraan']  ?? '____',
            'warna'    => $fd['warna_kendaraan']  ?? '____',
            'no_rangka'=> $fd['no_rangka']        ?? '___________________________',
            'no_mesin' => $fd['no_mesin']         ?? '___________________________',
            'no_polisi'=> $fd['no_polisi']        ?? '____',
            'no_bpkb'  => $fd['no_bpkb']         ?? '___________________________',
            'atas_nama'=> $fd['atas_nama_bpkb']   ?? '___________________________',
        ];

        $this->buildCommonStart($section, $vars);
        $this->addPasal4Kendaraan($section, $kendaraan);
        $this->addPasal5Kendaraan($section);
        $this->buildCommonEnd($section, $vars);

        return $this->save($phpWord, 'pk-kendaraan-');
    }

    // =========================================================================
    //  PRIVATE — bootstrap & save
    // =========================================================================

    private function bootstrap(LoanApplication $app, ?Loan $loan, string $nomorPk): array
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection($this->sectionStyle);

        $fd = array_merge($app->form_data ?? [], $app->doc_overrides ?? []);
        $member = $app->member;
        $disbDate = $loan?->disbursed_date ?? Carbon::parse($app->trx_date);
        if (!($disbDate instanceof Carbon)) $disbDate = Carbon::parse($disbDate);

        $bulanId = ['','Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
        $hariId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

        $principal = (int)($loan?->principal ?? $app->principal ?? 0);
        $tenor     = (int)($loan?->tenor_months ?? $app->tenor_months ?? 0);
        $bunga     = (float)($loan?->interest_monthly_percent ?? $app->interest_monthly_percent ?? 0);
        $angsuran  = (int)($loan?->monthly_installment ?? 0);

        $tglLunas = $disbDate->copy()->addMonths($tenor);

        $vars = [
            'nomorPk'    => $nomorPk,
            'hari'       => $hariId[$disbDate->dayOfWeek],
            'tglStr'     => $disbDate->day . ' ' . $bulanId[$disbDate->month] . ' ' . $disbDate->year,
            'thnStr'     => ucwords(terbilang($disbDate->year - 2000)),
            'thnFull'    => ucwords(terbilang($disbDate->year)),
            'dueDay'     => $disbDate->day,
            'tglLunasStr'=> $tglLunas->day . ' ' . $bulanId[$tglLunas->month] . ' ' . $tglLunas->year,
            'nama'       => $fd['nama_lengkap']  ?? $member?->name     ?? '__________________',
            'pekerjaan'  => $fd['pekerjaan']     ?? '__________________',
            'jalan'      => $fd['jalan']         ?? $fd['alamat'] ?? $member?->address ?? '__________________',
            'rt'         => $fd['rt']            ?? '000',
            'rw'         => $fd['rw']            ?? '000',
            'ktp'        => $fd['no_ktp']        ?? '__________________',
            'kelurahan'  => $fd['kelurahan']     ?? '__________________',
            'kecamatan'  => $fd['kecamatan']     ?? '__________________',
            'kota'       => $fd['kota']          ?? '__________________',
            'namaIstri'  => $fd['nama_pasangan'] ?? $fd['nama_suami'] ?? '__________________',
            'alamatIstri'=> $fd['alamat_pasangan'] ?? '__________________',
            'rtIstri'    => $fd['rt_pasangan']   ?? '000',
            'rwIstri'    => $fd['rw_pasangan']   ?? '000',
            'principal'  => $principal,
            'principalText' => ucwords(terbilang($principal)) . ' Rupiah',
            'tenor'      => $tenor,
            'tenorText'  => ucwords(terbilang($tenor)),
            'bunga'      => $bunga,
            'bungaTahunan' => round($bunga * 12),
            'angsuran'   => $angsuran,
            'angsuranText' => ucwords(terbilang($angsuran)),
            'tujuan'     => $fd['tujuan_pembiayaan'] ?? $app->notes ?? '___________________________',
            'provisiPct' => $app->loanProduct?->provision_rate_percent ?? 7,
        ];

        return [$phpWord, $section, $vars];
    }

    private function save(PhpWord $phpWord, string $prefix): string
    {
        $dir = storage_path('app/temp');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/' . $prefix . uniqid() . '.docx';
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);
        return $path;
    }

    // =========================================================================
    //  PRIVATE — sections common to both PK types
    // =========================================================================

    private function buildCommonStart(object $section, array $v): void
    {
        $this->addKop($section);
        $this->addTitle($section, $v['nomorPk']);
        $this->addPembuka($section, $v);
        $this->addParaPihak($section, $v);
        $this->addPasal1($section, $v);
        $this->addPasal2($section, $v);
        $this->addPasal3($section, $v);
    }

    private function buildCommonEnd(object $section, array $v): void
    {
        $this->addPasal6($section);
        $this->addPasal7($section);
        $this->addPasal8($section, $v);
        $this->addPasal9($section);
        $this->addPasal10($section, $v);
        $this->addPasal11($section);
        $this->addPasal12($section, $v);
        $this->addPasal13($section);
        $this->addPenutup($section, $v);
        $this->addSignature($section, $v);
    }

    // ─── Kop surat ───────────────────────────────────────────────────────────

    private function addKop(object $section): void
    {
        $logoPath = public_path('images/logo-ksp.jpeg');

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $row   = $table->addRow(Converter::cmToTwip(2.5));

        // Logo cell
        $logoCell = $row->addCell(Converter::cmToTwip(3), ['borderSize' => 0]);
        if (file_exists($logoPath)) {
            $logoCell->addImage($logoPath, [
                'width'  => Converter::cmToPoint(2.2),
                'height' => Converter::cmToPoint(2.2),
                'alignment' => 'center',
            ]);
        }

        // Company info cell
        $infoCell = $row->addCell(Converter::cmToTwip(13), ['borderSize' => 0, 'valign' => 'center']);
        $infoCell->addText(
            'KSP SARANA MITRA MANDIRI',
            ['name' => 'Times New Roman', 'size' => 15, 'bold' => true],
            ['alignment' => 'center', 'spaceAfter' => 40]
        );
        $infoCell->addText(
            'Jalan RS Fatmawati Komplek Duta Mas Blok B1 No.16, Kel. Cipete Utara',
            ['name' => 'Times New Roman', 'size' => 9],
            ['alignment' => 'center', 'spaceAfter' => 20]
        );
        $infoCell->addText(
            'Kec. Kebayoran Baru, Kota Jakarta Selatan 12150',
            ['name' => 'Times New Roman', 'size' => 9],
            ['alignment' => 'center', 'spaceAfter' => 20]
        );
        $infoCell->addText(
            'No. Telepon : 0812 1635 217',
            ['name' => 'Times New Roman', 'size' => 9],
            ['alignment' => 'center', 'spaceAfter' => 0]
        );

        // Spacer cell
        $row->addCell(Converter::cmToTwip(3), ['borderSize' => 0]);

        // Double underline
        $section->addTextBreak(0);
        $section->addText('', $this->fNormal, [
            'spaceAfter' => 80,
            'borderBottomSize' => 24,
            'borderBottomColor' => '000000',
            'borderTopSize' => 6,
            'borderTopColor' => '000000',
        ]);
    }

    // ─── Title ───────────────────────────────────────────────────────────────

    private function addTitle(object $section, string $nomorPk): void
    {
        $section->addTextBreak(1);
        $section->addText('PERJANJIAN KREDIT', $this->fTitle, $this->pCenter);
        $section->addText('Nomor ' . $nomorPk, $this->fNormal, ['alignment' => 'center', 'spaceAfter' => 120]);
    }

    // ─── Pembuka ─────────────────────────────────────────────────────────────

    private function addPembuka(object $section, array $v): void
    {
        $section->addText(
            'Pada hari ini, ' . $v['hari'] . ', tanggal ' . $v['tglStr'] .
            ' Tahun Dua Ribu ' . $v['thnStr'] . ' (' . $v['thnFull'] . ') ' .
            'diadakan Perjanjian Kredit yang kemudian disebut "perjanjian" antara :',
            $this->fNormal, $this->pJustify
        );
    }

    // ─── Para Pihak ──────────────────────────────────────────────────────────

    private function addParaPihak(object $section, array $v): void
    {
        // Pihak I
        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText('I.   ', $this->fBold);
        $run->addText('Koperasi Sarana Mitra Mandiri, suatu perusahaan pembiayaan yang berbentuk ' .
            'Koperasi Simpan Pinjam berkedudukan di Jakarta, dalam hal ini diwakili oleh Go Tji Seng, ' .
            'dalam kedudukannya sebagai Ketua Koperasi, sah mewakili Koperasi. ' .
            'selanjutnya disebut sebagai ', $this->fNormal);
        $run->addText('Pihak Pertama (Koperasi)', $this->fBold);
        $run->addText('.', $this->fNormal);

        // Pihak II
        $run2 = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run2->addText('II.  ', $this->fBold);
        $run2->addText($v['nama'], $this->fBold);
        $run2->addText(', pekerjaan ', $this->fNormal);
        $run2->addText($v['pekerjaan'], $this->fBold);
        $run2->addText(', bertempat tinggal di Jalan ' . $v['jalan'] .
            ' Rukun Tetangga ' . $v['rt'] . ', Rukun Warga ' . $v['rw'] .
            ', pemegang Kartu Tanda Penduduk Nomor ', $this->fNormal);
        $run2->addText($v['ktp'], $this->fBold);
        $run2->addText(', Desa/Kelurahan ' . $v['kelurahan'] .
            ', Kecamatan ' . $v['kecamatan'] . ', Kota ' . $v['kota'] .
            ', yang menurut keterangannya bertindak untuk dan atas nama ', $this->fNormal);
        $run2->addText('DIRI SENDIRI', $this->fBold);
        $run2->addText(' dan dalam melakukan tindakan hukum dalam perjanjian ini telah mendapat persetujuan dari ' .
            $v['namaIstri'] . ' bertempat tinggal di ' . $v['alamatIstri'] .
            ' RT ' . $v['rtIstri'] . ' RW ' . $v['rwIstri'] .
            ', dalam hal ini berkedudukan sebagai istri yang turut serta menandatangani perjanjian ini ' .
            'sebagai bukti persetujuannya, untuk selanjutnya disebut ', $this->fNormal);
        $run2->addText('Pihak Kedua (Debitur)', $this->fBold);
        $run2->addText('.', $this->fNormal);

        $section->addText(
            'Selanjutnya Pihak Pertama (Koperasi) dan Pihak Kedua (Debitur) disebut "Para Pihak".',
            $this->fNormal, $this->pJustify
        );
        $section->addText(
            'Sebelum menandatangani Perjanjian ini, Para Pihak bertindak sebagaimana tersebut diatas dan ' .
            'menerangkan dengan ini telah mufakat dan setuju untuk membuat dan menandatangani Perjanjian ' .
            'dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut :',
            $this->fNormal, $this->pJustify
        );
    }

    // ─── Helper: Pasal header ─────────────────────────────────────────────────

    private function pasalHeader(object $section, string $num, string $title): void
    {
        $section->addTextBreak(1);
        $section->addText('Pasal ' . $num, $this->fPasal, $this->pCenter);
        $section->addText($title, $this->fUl, ['alignment' => 'center', 'spaceAfter' => 80]);
    }

    // ─── Helper: lettered list item ───────────────────────────────────────────

    private function listItem(object $section, string $letter, string $text,
                              array $boldParts = [], bool $newPara = false): void
    {
        if ($newPara) {
            $section->addText('', $this->fNormal, ['spaceAfter' => 40]);
        }
        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText($letter . '.   ', $this->fNormal);
        if (empty($boldParts)) {
            $run->addText($text, $this->fNormal);
        } else {
            // Split text around bold segments
            $remaining = $text;
            foreach ($boldParts as $boldText) {
                $pos = strpos($remaining, $boldText);
                if ($pos !== false) {
                    if ($pos > 0) $run->addText(substr($remaining, 0, $pos), $this->fNormal);
                    $run->addText($boldText, $this->fBold);
                    $remaining = substr($remaining, $pos + strlen($boldText));
                }
            }
            if ($remaining !== '') $run->addText($remaining, $this->fNormal);
        }
    }

    // =========================================================================
    //  PASALS
    // =========================================================================

    private function addPasal1(object $section, array $v): void
    {
        $this->pasalHeader($section, '1', 'Jumlah dan Tujuan Penggunaan Fasilitas Kredit');

        $rp = 'Rp ' . number_format($v['principal'], 0, ',', '.');
        $section->addText(
            'Pihak Pertama (Koperasi) bersedia memberikan Fasilitas Kredit sebesar ' .
            $rp . ', (' . $v['principalText'] . ') yang terdiri dari :',
            $this->fNormal, $this->pJustify
        );

        $this->listItem($section, 'a',
            'Fasilitas Pinjaman Berjangka dengan Plafond sebesar ' . $rp . ',- (' .
            $v['principalText'] . ') dengan jangka waktu ' . $v['tenor'] . ' (' . $v['tenorText'] .
            ') bulan, terhitung sejak tanggal ' . $v['tglStr'] . ' dan karenanya seluruh hutang ' .
            'harus dibayar lunas oleh Pihak Kedua (Debitur) kepada Pihak Pertama (Koperasi) pada ' .
            'tanggal ' . $v['tglLunasStr'] . ';');

        $this->listItem($section, 'b',
            'Pelunasan mana meliputi hutang pokok, bunga, biaya administrasi dan biaya-biaya lainnya.');

        $this->listItem($section, 'c',
            'Pihak Kedua (Debitur) dengan ini mengaku telah mendapat Fasilitas Kredit yang sah karena ' .
            'uang yang telah dipinjam itu untuk kepentingannya dan telah menerima lengkap dari Pihak ' .
            'Pertama (Koperasi), untuk penerimaan surat Perjanjian ini berlaku sebagai tanda penerimaan yang sah.');

        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText('d.   Kredit ini hanya boleh dipergunakan untuk tujuan "', $this->fNormal);
        $run->addText($v['tujuan'], $this->fBold);
        $run->addText('" yang akan dipergunakan oleh Pihak Kedua (Debitur) sesuai dengan rencana ' .
            'yang telah disetujui oleh Pihak Pertama (Koperasi).', $this->fNormal);

        $this->listItem($section, 'e',
            'Setiap waktu Pihak Pertama (Koperasi) dan/atau Pihak Ketiga yang ditunjuk berhak memeriksa ' .
            'penggunaan kredit dalam rangka pengawasan dan pengamanan kredit tersebut, dan Pihak Kedua ' .
            '(Debitur) wajib membayar seluruh pinjaman beserta bunga dan ongkos-ongkos lain yang timbul ' .
            'karena perjanjian ini.');
    }

    private function addPasal2(object $section, array $v): void
    {
        $this->pasalHeader($section, '2', 'Bunga, Administrasi, dan Penalty');
        $section->addText(
            'Terhadap Fasilitas Kredit yang diberikan dibebankan :',
            $this->fNormal, $this->pJustify
        );

        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText('a.   ', $this->fNormal);
        $run->addText('Bunga', $this->fBold);
        $run->addText(' : untuk Fasilitas Kredit kepada Pihak Kedua (Debitur) dibebankan suku bunga pinjaman sebesar ', $this->fNormal);
        $run->addText($v['bungaTahunan'] . '% (' . ucwords(terbilang((int)$v['bungaTahunan'])) . ' persen)', $this->fBold);
        $run->addText(' pertahun flat atas seluruh jumlah Fasilitas Kredit yang diberikan.', $this->fNormal);

        $run2 = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run2->addText('b.   ', $this->fNormal);
        $run2->addText('Biaya Administrasi', $this->fBold);
        $run2->addText(' : untuk Fasilitas Kredit dikenakan biaya Rp 20.000.000,- (Dua Puluh Juta Rupiah) perakad.', $this->fNormal);

        $run3 = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run3->addText('c.   ', $this->fNormal);
        $run3->addText('Biaya Provisi', $this->fBold);
        $run3->addText(' : untuk Fasilitas Kredit dikenakan biaya provisi sebesar ', $this->fNormal);
        $run3->addText($v['provisiPct'] . '%', $this->fBold);
        $run3->addText(' perakad atas seluruh jumlah Fasilitas Kredit yang diberikan.', $this->fNormal);

        $this->listItem($section, 'd',
            'Pihak Kedua (Debitur) menyatakan persetujuannya, apabila dalam batas waktu berlakunya kredit ini ' .
            'belum melunasi seluruh jumlah kredit beserta bunganya maka Pihak Pertama (Koperasi) berhak ' .
            'memperhitungkan denda (Penalty overdue) terhadap Pihak Kedua (Debitur) sebagaimana tercantum pada pasal 3.');
    }

    private function addPasal3(object $section, array $v): void
    {
        $this->pasalHeader($section, '3', 'Batas Waktu Penarikan Dana/atau Penggunaan Fasilitas Kredit');
        $section->addText('Untuk Fasilitas Kredit berlaku ketentuan sebagai berikut :', $this->fNormal, $this->pJustify);

        $rp = 'Rp ' . number_format($v['angsuran'], 0, ',', '.');
        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText('a.   Pembayaran Angsuran Pokok Pinjaman dan Bunga akan dilakukan setiap tanggal ', $this->fNormal);
        $run->addText((string)$v['dueDay'], $this->fBold);
        $run->addText(', tiap-tiap bulannya sebesar ', $this->fNormal);
        $run->addText($rp . ',- (' . $v['angsuranText'] . ')', $this->fBold);
        $run->addText(' yang pertama kalinya pada tanggal ' . $v['tglStr'] .
            ' dan harus sudah lunas seluruhnya selambat-lambatnya pada tanggal ' . $v['tglLunasStr'] . '.', $this->fNormal);

        $this->listItem($section, 'b',
            'Apabila Pihak Kedua (Debitur) melunasi seluruh pinjamannya sebelum tanggal jatuh tempo maka ' .
            'dikenakan denda penalty sebesar 5 % (lima persen) dari sisa outstanding/baki debet.');

        $this->listItem($section, 'c',
            'Besarnya jumlah angsuran pokok pinjaman dan bunga pinjaman adalah sesuai dengan daftar ' .
            'pembayaran yang telah ditetapkan.');

        $this->listItem($section, 'd',
            'Apabila Pihak Kedua (Debitur) melakukan kelalaian untuk setiap bulannya tidak melakukan ' .
            'pembayaran angsuran, maka Pihak Kedua (Debitur) akan dikenakan bunga yang besarnya ditentukan ' .
            'oleh Pihak Pertama (Koperasi) dan wajib membayar seluruh kewajiban pinjaman secara seketika ' .
            'dan sekaligus lunas.');

        $run2 = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run2->addText('e.   Apabila Pihak Kedua (Debitur) melakukan keterlambatan atas pembayaran Angsuran pada setiap bulannya, maka Pihak Kedua (Debitur) akan dikenakan denda oleh Pihak Pertama (Koperasi) sebesar ', $this->fNormal);
        $run2->addText('0,3 % (nol koma tiga) persen per hari', $this->fBold);
        $run2->addText('.', $this->fNormal);
    }

    private function addPasal4Sertipikat(object $section, array $a): void
    {
        $this->pasalHeader($section, '4', 'Agunan dan/atau Jaminan');
        $section->addText(
            'Untuk menambah kepastian tentang pembayaran kembali dari kredit tersebut, Pihak Kedua (Debitur) ' .
            'memberikan jaminan kebendaan kepada Pihak Pertama (Koperasi), berupa Tanah dan Bangunan sebagai berikut :',
            $this->fNormal, $this->pJustify
        );
        $section->addText(
            'Hak Milik, Nomor ' . $a['no_hak'] . '/' . $a['kelurahan'] . ', dengan Nomor Identifikasi Bidang ' .
            'Tanah (NIB) : ' . $a['nib'] . '; dan SPPT PBB Nomor Objek Pajak (NOP) : ' . $a['nop'] . '; yang terletak di :',
            $this->fNormal, ['alignment' => 'both', 'spaceAfter' => 60, 'lineHeight' => 1.5,
                             'indentation' => ['left' => 360]]
        );

        $table = $section->addTable(['borderSize' => 0, 'leftMargin' => 360]);
        foreach ([
            'Provinsi'   => $a['provinsi'],
            'Kabupaten'  => $a['kab'],
            'Kecamatan'  => $a['kec'],
            'Kelurahan'  => $a['kel'],
            'Jalan'      => $a['jalan'],
        ] as $label => $val) {
            $row = $table->addRow();
            $row->addCell(2000)->addText($label, $this->fNormal);
            $row->addCell(300)->addText(':', $this->fNormal);
            $row->addCell(7000)->addText($val, $this->fBold);
        }

        $section->addTextBreak(0);
        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360]]);
        $run->addText('Sertifikat terdaftar atas nama ', $this->fNormal);
        $run->addText($a['atas_nama'], $this->fBold);
        $run->addText('. Hanya Pihak Pertama (Koperasi) yang dapat mempertimbangkan bila jaminan harus ditambah atau dikurangi.', $this->fNormal);
    }

    private function addPasal4Kendaraan(object $section, array $k): void
    {
        $this->pasalHeader($section, '4', 'Agunan dan/atau Jaminan');
        $section->addText(
            'Untuk menambah kepastian tentang pembayaran kembali dari kredit tersebut, Pihak Kedua (Debitur) ' .
            'memberikan jaminan kebendaan kepada Pihak Pertama (Koperasi), berupa 1 (satu) Unit Kendaraan Roda 4 (empat) ' .
            'berikut BPKB-nya dengan spesifikasi sebagai berikut :',
            $this->fNormal, $this->pJustify
        );

        $table = $section->addTable(['borderSize' => 0, 'leftMargin' => 360]);
        foreach ([
            'Merk/Type'   => $k['merk'],
            'Tahun'       => $k['tahun'],
            'Warna'       => $k['warna'],
            'No. Rangka'  => $k['no_rangka'],
            'No. Mesin'   => $k['no_mesin'],
            'No. Polisi'  => $k['no_polisi'],
            'BPKB'        => $k['no_bpkb'],
            'Atas Nama'   => $k['atas_nama'],
        ] as $label => $val) {
            $row = $table->addRow();
            $row->addCell(2000)->addText($label, $this->fNormal);
            $row->addCell(300)->addText(':', $this->fNormal);
            $row->addCell(7000)->addText($val, $this->fBold);
        }

        $section->addTextBreak(0);
        $section->addText(
            'Hanya Pihak Pertama (Koperasi) yang dapat mempertimbangkan bila jaminan harus ditambah atau dikurangi.',
            $this->fNormal, $this->pJustify
        );
    }

    private function addPasal5Sertipikat(object $section): void
    {
        $this->pasalHeader($section, '5', 'Asuransi');
        $section->addText(
            'Selama Pihak Kedua (Debitur) masih berhutang kepada Pihak Pertama (Koperasi) maka Pihak Kedua (Debitur) ' .
            'diwajibkan mengikuti Asuransi KEMATIAN, KEBAKARAN, dan lain sebagainya sesuai yang telah ditentukan dan ' .
            'ditetapkan oleh Pihak Pertama (Koperasi), dan Pihak Pertama (Koperasi) berhak menerima uang ganti rugi ' .
            'kerugian tersebut, yang akan dipergunakan untuk melunasi hutang Pihak Kedua (Debitur) kepada Pihak ' .
            'Pertama (Koperasi) berikut dengan bunga-bunga dan biaya-biaya yang bertalian.',
            $this->fNormal, $this->pJustify
        );
    }

    private function addPasal5Kendaraan(object $section): void
    {
        $this->pasalHeader($section, '5', 'Asuransi');
        $section->addText(
            'Selama Pihak Kedua (Debitur) masih berhutang kepada Pihak Pertama (Koperasi) maka Pihak Kedua (Debitur) ' .
            'diwajibkan mengikuti Asuransi Kendaraan (Kehilangan) dan lain sebagainya sesuai yang telah ditentukan ' .
            'oleh Pihak Pertama (Koperasi), dan Pihak Pertama (Koperasi) berhak menerima uang ganti rugi kerugian ' .
            'tersebut, yang akan dipergunakan untuk melunasi hutang Pihak Kedua (Debitur) kepada Pihak Pertama ' .
            '(Koperasi) berikut dengan bunga-bunga dan biaya-biaya yang bertalian.',
            $this->fNormal, $this->pJustify
        );
    }

    private function addPasal6(object $section): void
    {
        $this->pasalHeader($section, '6', 'Kejadian Kelalaian');
        $this->listItem($section, 'a',
            'Apabila Pihak Kedua (Debitur) berada dalam keadaan lalai atau berhenti membayar, maka Pihak Pertama ' .
            '(Koperasi) tanpa perlu mendapat persetujuan lebih dahulu dari Pihak Kedua (Debitur), berhak untuk ' .
            'menjual dan/atau memindahtangankan, atau dengan cara cessie/pengalihan piutang dan/atau menjual ' .
            'secara lelang atas barang jaminan baik di muka umum maupun di bawah tangan.');
        $this->listItem($section, 'b',
            'Hasil bersih dari penjualan jaminan tersebut, yaitu setelah dikurangi biaya-biaya yang diperlukan ' .
            'untuk tindakan tersebut, akan dipergunakan untuk melunasi seluruh hutang Pihak Kedua (Debitur).');
        $this->listItem($section, 'c',
            'Apabila dari hasil penjualan jaminan ternyata tidak mencukupi, sisa hutang tersebut tetap menjadi ' .
            'kewajiban Pihak Kedua (Debitur) untuk membayar dan melunasinya.');
    }

    private function addPasal7(object $section): void
    {
        $this->pasalHeader($section, '7', 'Berakhirnya Perjanjian');
        $section->addText(
            'Menyimpang dari apa yang ditentukan dalam pasal 1 ayat 4 tersebut di atas, jika terjadi salah satu ' .
            'sebab yang tersebut di bawah ini, maka Pihak Pertama (Koperasi) berhak untuk seketika tanpa somasi ' .
            'lagi mengakhiri perjanjian ini dan menuntut pembayaran dengan seketika dan sekaligus lunas, bilamana :',
            $this->fNormal, $this->pJustify
        );
        foreach ([
            'a' => 'Pihak Kedua (Debitur) tidak atau tidak cukup melaksanakan salah satu kewajibannya dalam perjanjian ini;',
            'b' => 'Pihak Kedua (Debitur) meninggal dunia;',
            'c' => 'Pihak Kedua (Debitur) jatuh pailit dan/atau karena apapun juga kehilangan haknya untuk mengurus sendiri harta kekayaannya;',
            'd' => 'Pihak Kedua (Debitur) mendapat pengunduran pembayaran (surseance van betaling);',
            'e' => 'Jaminan sebagaimana pada pasal 4 terindikasi sengketa, dijual, diubah, dipindahtangankan, atau dijaminkan oleh Pihak Kedua (Debitur) kepada pihak lain;',
            'f' => 'Harta kekayaan Pihak Kedua (Debitur) dikenakan sitaan;',
            'g' => 'Kekayaan Pihak Kedua (Debitur) sedemikian rupa sehingga menurut pertimbangan Pihak Pertama (Koperasi) tidak dapat membayar hutangnya lagi;',
            'h' => 'Izin usaha Pihak Kedua (Debitur) dicabut atau jaminan disita pihak lain;',
            'i' => 'Pihak Kedua (Debitur) tidak memenuhi salah satu atau lebih kewajiban sebagaimana ditentukan dalam perjanjian ini;',
            'j' => 'Terjadi peristiwa force majeur yang tidak dapat diatasi Pihak Pertama (Koperasi);',
            'k' => 'Kekayaan Pihak Kedua (Debitur) menjadi berkurang sehingga tidak lagi merupakan jaminan yang cukup.',
        ] as $letter => $text) {
            $this->listItem($section, $letter, $text);
        }
    }

    private function addPasal8(object $section, array $v): void
    {
        $this->pasalHeader($section, '8', 'Pernyataan');
        foreach ([
            'a' => 'Pihak Kedua (Debitur) menyetujui bahwa pembukuan Pihak Pertama (Koperasi) selalu menjadi dasar untuk menetapkan jumlah-jumlah yang terhutang oleh Pihak Kedua (Debitur) dan Pihak Kedua (Debitur) melepaskan haknya untuk mengajukan keberatan atas pembuktian tersebut.',
            'b' => 'Pihak Kedua (Debitur) memberi hak dan kuasa penuh kepada Pihak Pertama (Koperasi) untuk membebani rekening Pihak Kedua (Debitur) guna pembayaran-pembayaran bunga pinjaman, angsuran/cicilan pokok pinjaman, provisi, biaya administrasi dan biaya-biaya lainnya.',
            'c' => 'Pihak Kedua (Debitur) mengikat diri terhadap Pihak Pertama (Koperasi) untuk menyampaikan informasi-informasi lain apapun dan memberitahukan tentang setiap perubahan besar dalam keuangan.',
            'd' => 'Pihak Kedua (Debitur) tidak ada suatu perkara perdata, tata usaha negara, tuntutan pajak, penyidikan maupun perkara pidana yang sedang berlangsung yang dapat mempengaruhi keadaan keuangannya.',
            'e' => 'Pihak Kedua (Debitur) wajib memberitahukan kepada Pihak Pertama (Koperasi) tentang adanya permohonan pernyataan pailit yang diajukan selambat-lambatnya 3 (tiga) hari sejak mengetahuinya.',
            'f' => 'Pihak Kedua (Debitur) memberikan semua dokumen, data, dan keterangan kepada Pihak Pertama (Koperasi) adalah yang sebenarnya dan dapat dipertanggungjawabkan keasliannya.',
            'g' => 'Pihak Kedua (Debitur) menyatakan persetujuannya dalam hal terjadi salah satu kelalaian maka Pihak Pertama (Koperasi) berhak menuntut pembayaran atas jumlah terhutang dengan menggunakan jasa Pihak Ketiga yang ditunjuk.',
            'h' => 'Pihak Kedua (Debitur) menyatakan persetujuannya apabila jaminan disita oleh suatu instansi pemerintah, Pihak Pertama (Koperasi) berhak mengambil alih dan menguasai jaminan tersebut tanpa perlu mendapat persetujuan lebih dulu dari Pihak Kedua (Debitur).',
        ] as $letter => $text) {
            $this->listItem($section, $letter, $text);
        }
    }

    private function addPasal9(object $section): void
    {
        $this->pasalHeader($section, '9', 'Larangan Bagi Pihak Kedua (Debitur)');
        $section->addText(
            'Selama Pihak Kedua (Debitur) belum membayar lunas hutang dan/atau penggunaan Fasilitas Kredit ' .
            'belum berakhir, Pihak Kedua (Debitur) dilarang melakukan hal-hal di bawah ini tanpa persetujuan ' .
            'tertulis dahulu dari Pihak Pertama (Koperasi) :',
            $this->fNormal, $this->pJustify
        );
        foreach ([
            'a' => 'Pihak Kedua (Debitur) mengajukan permohonan pernyataan pailit kepada Pengadilan Niaga.',
            'b' => 'Pihak Kedua (Debitur) mengalihkan, menjual, menyewakan jaminan kepada Pihak Ketiga atau pihak lain.',
            'c' => 'Pihak Kedua (Debitur) menggunakan jaminan untuk suatu alat tindak pidana atau yang melanggar ketentuan Peraturan Perundang-Undangan yang berlaku.',
            'd' => 'Pihak Kedua (Debitur) merubah bentuk jaminan sehingga tidak seperti bentuk awal jaminan.',
        ] as $letter => $text) {
            $this->listItem($section, $letter, $text);
        }
    }

    private function addPasal10(object $section, array $v): void
    {
        $this->pasalHeader($section, '10', 'Tempat Pembayaran');
        $section->addText(
            'Pembayaran dapat dilakukan kepada dan di Kantor "Pihak Pertama" yaitu Koperasi Simpan Pinjam ' .
            'Sarana Mitra Mandiri, Komplek Duta Mas Fatmawati Blok B1 No 16 Jakarta Selatan 12150 atau di alamat yang ditunjuk oleh Pihak Pertama (Koperasi).',
            $this->fNormal, $this->pJustify
        );
    }

    private function addPasal11(object $section): void
    {
        $this->pasalHeader($section, '11', 'Penyerahan dan Pengembalian Dokumen/Sertipikat');
        $this->listItem($section, 'a',
            'Pihak Kedua (Debitur) wajib menyerahkan semua bukti-bukti kepemilikan obyek pembiayaan kepada ' .
            'Pihak Pertama (Koperasi) untuk disimpan selama hutang belum dibayar lunas.');
        $this->listItem($section, 'b',
            'Dalam hal Pihak Kedua (Debitur) telah melunasi seluruh hutangnya, maka Pihak Pertama (Koperasi) ' .
            'berkewajiban untuk mengembalikan semua bukti-bukti kepemilikan obyek pembiayaan kepada Pihak Kedua ' .
            '(Debitur) paling lambat 14 (empat belas) hari kerja setelah proses selesai.');
    }

    private function addPasal12(object $section, array $v): void
    {
        $this->pasalHeader($section, '12', 'Ketentuan Lain');
        $this->listItem($section, 'a',
            'Jumlah hutang oleh Pihak Kedua (Debitur) kepada Pihak Pertama (Koperasi) merupakan hutang yang ' .
            'tidak dapat dibagi-bagi (Ondeelbare schuld) sehingga berdasarkan pasal 1300 KUHPerdata, setiap ' .
            'pengganti hak dari Pihak Kedua (Debitur) dapat dituntut untuk seluruh jumlah hutang.');
        $this->listItem($section, 'b',
            'Semua kuasa yang termaktub di dalam perjanjian ini merupakan bagian yang terpenting yang tidak ' .
            'dapat dipisahkan dari perjanjian ini dan tidak dapat dicabut berdasarkan pasal 1813 KUHPerdata.');

        $run = $section->addTextRun(['alignment' => 'both', 'spaceAfter' => 80, 'lineHeight' => 1.5,
            'indentation' => ['left' => 360, 'hanging' => 360]]);
        $run->addText('c.   Semua surat menyurat atau pemberitahuan-pemberitahuan yang harus dikirim ke alamat :', $this->fNormal);

        $table = $section->addTable(['leftMargin' => 360]);
        $row = $table->addRow();
        $left = $row->addCell(5000);
        $left->addText('Pihak Pertama (Koperasi)', $this->fBold, ['spaceAfter' => 40]);
        $left->addText('Koperasi Simpan Pinjam Sarana Mitra Mandiri', $this->fNormal, ['spaceAfter' => 20]);
        $left->addText('Komplek Dutamas Fatmawati Blok B1 No. 16', $this->fNormal, ['spaceAfter' => 20]);
        $left->addText('Cipete Utara, Kebayoran Baru, Jakarta Selatan', $this->fNormal, ['spaceAfter' => 20]);
        $left->addText('Telp : 021 - ......................................', $this->fNormal);
        $right = $row->addCell(5000);
        $right->addText('Pihak Kedua (Debitur)', $this->fBold, ['spaceAfter' => 40]);
        $right->addText('Nama   : ' . $v['nama'], $this->fNormal, ['spaceAfter' => 20]);
        $right->addText('Alamat : ' . $v['jalan'], $this->fNormal, ['spaceAfter' => 20]);
        $right->addText('           ' . $v['kelurahan'] . ', ' . $v['kecamatan'], $this->fNormal, ['spaceAfter' => 20]);
        $right->addText('Telp   : ......................................', $this->fNormal);

        $this->listItem($section, 'd',
            'Hal-hal lain yang belum diatur dalam Perjanjian Kredit ini akan diatur oleh Para Pihak dalam ' .
            'suatu persetujuan tersendiri yang merupakan satu kesatuan dan tidak dapat terpisahkan dari Perjanjian ini.');
    }

    private function addPasal13(object $section): void
    {
        $this->pasalHeader($section, '13', 'Hukum Yang Dipakai dan Yurisdiksi Pengadilan');
        $this->listItem($section, 'a',
            'Mengenai Perjanjian ini dan segala akibatnya, kedua belah pihak memilih tempat kedudukan hukum ' .
            '(domisili) umum yang tetap dan secara hukum pada Kantor Panitera Pengadilan Negeri yang berwenang ' .
            'atas wilayah tempat Kantor Pihak Pertama (Koperasi) yang memberi kredit.');
        $this->listItem($section, 'b',
            'Dengan tidak mengurangi hak dari Pihak Pertama (Koperasi) untuk memohon pelaksanaan (eksekusi) ' .
            'dari Perjanjian ini atau mengajukan tuntutan hukum terhadap Pihak Kedua (Debitur) melalui ' .
            'Pengadilan Negeri lainnya dalam Wilayah Republik Indonesia.');
    }

    private function addPenutup(object $section, array $v): void
    {
        $section->addTextBreak(1);
        $section->addText(
            'Demikian Perjanjian Kredit ini dibuat dan telah disesuaikan dengan ketentuan Peraturan ' .
            'Perundang-undangan, ditandatangani di Jakarta, pada hari dan tanggal seperti tersebut ' .
            'pada bagian awal Perjanjian ini.',
            $this->fNormal, $this->pJustify
        );
    }

    private function addSignature(object $section, array $v): void
    {
        $section->addTextBreak(1);
        $table = $section->addTable(['borderSize' => 0]);
        $row   = $table->addRow(Converter::cmToTwip(5));

        $left = $row->addCell(5000, ['borderSize' => 0, 'valign' => 'top']);
        $left->addText('"Pihak Pertama"', $this->fBold, ['spaceAfter' => 40]);
        $left->addText('KSP SARANA MITRA MANDIRI', $this->fBold, ['spaceAfter' => 200]);
        $left->addTextBreak(3);
        $left->addText('Go Tji Seng', $this->fBold, ['spaceAfter' => 20]);
        $left->addText('.....................................................', $this->fNormal, ['spaceAfter' => 20]);
        $left->addText('Ketua Koperasi', $this->fBold);

        $right = $row->addCell(5000, ['borderSize' => 0, 'valign' => 'top']);
        $right->addText('"Pihak Kedua"', $this->fBold, ['spaceAfter' => 200]);
        $right->addTextBreak(3);
        $right->addText('.....................................................', $this->fNormal, ['spaceAfter' => 40]);
        $right->addText('Persetujuan Suami / Istri', $this->fNormal, ['spaceAfter' => 200]);
        $right->addTextBreak(3);
        $right->addText('.....................................................', $this->fNormal);

        // Witnesses
        $section->addTextBreak(2);
        $wTable = $section->addTable(['borderSize' => 0]);
        $wRow   = $wTable->addRow(Converter::cmToTwip(4));

        $w1 = $wRow->addCell(5000, ['borderSize' => 0]);
        $w1->addText('Saksi,', $this->fNormal, ['spaceAfter' => 200]);
        $w1->addTextBreak(2);
        $w1->addText('(..........................................................)', $this->fNormal);

        $w2 = $wRow->addCell(5000, ['borderSize' => 0]);
        $w2->addText('Saksi,', $this->fNormal, ['spaceAfter' => 200]);
        $w2->addTextBreak(2);
        $w2->addText('(..........................................................)', $this->fNormal);
    }
}
