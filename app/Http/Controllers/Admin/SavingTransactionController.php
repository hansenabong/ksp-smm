<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingTransaction;
use Barryvdh\DomPDF\Facade\Pdf;

class SavingTransactionController extends Controller
{
    public function show(SavingTransaction $tx)
    {
        $tx->load(['member', 'savingProduct', 'cashAccount', 'creator', 'voider']);
        return view('admin.savings.show', compact('tx'));
    }

    public function voucher(SavingTransaction $tx)
    {
        $tx->load(['member', 'savingProduct', 'cashAccount', 'creator']);
        $pdf = Pdf::loadView('admin.savings.voucher-pdf', compact('tx'))
            ->setPaper('a4', 'portrait');
        $filename = 'voucher-simpanan-' . $tx->id . '.pdf';
        return $pdf->download($filename);
    }
}
