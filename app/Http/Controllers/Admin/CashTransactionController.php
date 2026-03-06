<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use Barryvdh\DomPDF\Facade\Pdf;

class CashTransactionController extends Controller
{
    public function show(CashTransaction $tx)
    {
        $tx->load(['fromCashAccount', 'toCashAccount', 'cashCategory', 'creator', 'voider']);
        return view('admin.cash.show', compact('tx'));
    }

    public function voucher(CashTransaction $tx)
    {
        $tx->load(['fromCashAccount', 'toCashAccount', 'cashCategory', 'creator']);
        $pdf = Pdf::loadView('admin.cash.voucher-pdf', compact('tx'))
            ->setPaper('a4', 'portrait');
        $filename = 'voucher-kas-' . $tx->code . '.pdf';
        return $pdf->download($filename);
    }
}
