<?php

namespace App\Http\Controllers\Admin\Collection;

use App\Http\Controllers\Controller;
use App\Models\CollectionLetter;
use App\Models\CollectionRecord;
use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CollectionLetterController extends Controller
{
    private const ALLOWED_TYPES = ['SP1', 'SP2', 'SP3', 'SOMASI', 'CESSIE'];

    /**
     * Create a collection letter record and redirect to its PDF.
     */
    public function store(Request $request, Loan $loan)
    {
        $data = $request->validate([
            'letter_type'   => 'required|in:SP1,SP2,SP3,SOMASI,CESSIE',
            'letter_date'   => 'required|date',
            'letter_number' => 'nullable|string|max:80',
            'notes'         => 'nullable|string|max:1000',
        ]);

        // Guard: only allow SP/SOMASI/CESSIE when customer has actual arrears
        $disbursed     = \Carbon\Carbon::parse($loan->disbursed_date);
        $monthsElapsed = min((int) $disbursed->diffInMonths(\Carbon\Carbon::today()), $loan->tenor_months);
        $arrears       = max(0, ($monthsElapsed * $loan->monthly_installment) - $loan->total_paid);
        if ($arrears <= 0) {
            return back()->withErrors(['letter_type' => 'Surat tidak dapat dibuat: nasabah tidak memiliki tunggakan (pembayaran on time).'])->withInput();
        }

        $letterNumber = filled($data['letter_number'] ?? null)
            ? $data['letter_number']
            : $this->generateNumber($data['letter_type'], $loan, $data['letter_date']);

        $letter = CollectionLetter::create([
            'loan_id'       => $loan->id,
            'letter_type'   => $data['letter_type'],
            'letter_number' => $letterNumber,
            'letter_date'   => $data['letter_date'],
            'created_by'    => auth()->id(),
            'notes'         => $data['notes'] ?? null,
        ]);

        // Bump escalation level on collection record
        $newLevel = CollectionLetter::TYPE_LEVEL[$data['letter_type']];
        $record   = CollectionRecord::firstOrNew(['loan_id' => $loan->id]);
        if ($newLevel > ($record->escalation_level ?? 0)) {
            $record->loan_id          = $loan->id;
            $record->escalation_level = $newLevel;
            $record->save();
        }

        return redirect()->route('admin.collection.letters.pdf', $letter)
            ->with('success', 'Surat berhasil dibuat.');
    }

    /**
     * Render PDF for a collection letter.
     */
    public function pdf(CollectionLetter $letter)
    {
        $letter->load(['loan.member', 'loan.application', 'creator']);
        $loan   = $letter->loan;
        $member = $loan->member;

        $viewMap = [
            'SP1'    => 'admin.collection.letters.sp1-pdf',
            'SP2'    => 'admin.collection.letters.sp2-pdf',
            'SP3'    => 'admin.collection.letters.sp3-pdf',
            'SOMASI' => 'admin.collection.letters.somasi-pdf',
            'CESSIE' => 'admin.collection.letters.cessie-pdf',
        ];

        $view = $viewMap[$letter->letter_type] ?? abort(404);

        $noPk = $loan->application?->notaris_deed_number
             ?: ('PK-KSP/' . str_pad($loan->id, 5, '0', STR_PAD_LEFT) . '/' . \Carbon\Carbon::parse($loan->disbursed_date)->format('Y'));

        // Build detailed addresses from application form_data
        $fd = $loan->application?->form_data ?? [];

        // Alamat tinggal (current residence)
        $alamatTinggal = collect([
            $fd['alamat'] ?? null,
            isset($fd['rt']) ? 'RT ' . $fd['rt'] : null,
            isset($fd['rw']) ? 'RW ' . $fd['rw'] : null,
            $fd['kelurahan'] ?? null,
            $fd['kecamatan'] ?? null,
            $fd['kota'] ?? null,
            $fd['kode_pos'] ?? null,
        ])->filter()->implode(', ') ?: ($member->address ?? 'Tempat');

        // Alamat jaminan/agunan (collateral address)
        $alamatJaminan = collect([
            $fd['komplek_agunan'] ?? null,
            $fd['jalan_agunan'] ?? null,
            isset($fd['blok_agunan']) ? 'Blok ' . $fd['blok_agunan'] : null,
            isset($fd['no_agunan']) ? 'No. ' . $fd['no_agunan'] : null,
            isset($fd['rt_agunan']) ? 'RT ' . $fd['rt_agunan'] : null,
            isset($fd['rw_agunan']) ? 'RW ' . $fd['rw_agunan'] : null,
            $fd['kelurahan_agunan'] ?? null,
            $fd['kecamatan_agunan'] ?? null,
            $fd['kota_agunan'] ?? null,
        ])->filter()->implode(', ');

        $statusAgunan = $fd['status_agunan'] ?? null;

        $pdf = Pdf::loadView($view, compact('letter', 'loan', 'member', 'noPk', 'alamatTinggal', 'alamatJaminan', 'statusAgunan', 'fd'))
            ->setPaper('a4', 'portrait');

        $filename = strtolower($letter->letter_type) . '-' . $loan->id . '-' . $letter->letter_date->format('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    // ──────────────────────────────────────────────────────────────────────────

    public static function previewNumber(string $type, string $date): string
    {
        $year  = \Carbon\Carbon::parse($date)->format('Y');

        // SP1/SP2/SP3 share one sequential counter; SOMASI and CESSIE are independent
        if (in_array($type, ['SP1', 'SP2', 'SP3'])) {
            $count = CollectionLetter::whereIn('letter_type', ['SP1', 'SP2', 'SP3'])
                ->whereYear('letter_date', $year)
                ->count() + 1;
        } else {
            $count = CollectionLetter::where('letter_type', $type)
                ->whereYear('letter_date', $year)
                ->count() + 1;
        }

        $prefix = match ($type) {
            'SP1'    => 'SP1',
            'SP2'    => 'SP2',
            'SP3'    => 'SP3',
            'SOMASI' => 'SOM',
            'CESSIE' => 'CES',
            default  => $type,
        };

        return $prefix . '/' . $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function generateNumber(string $type, Loan $loan, string $date): string
    {
        return self::previewNumber($type, $date);
    }
}
