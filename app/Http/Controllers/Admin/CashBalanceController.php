<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Cash\CashTransactionService;

class CashBalanceController extends Controller
{
    public function index(CashTransactionService $service)
    {
        $all = $service->getAllBalances();

        // Split: DEPOSITO accounts are shown as view-only and NOT included
        // in Total Saldo. They only flow into Total Saldo once funds are
        // transferred out to a regular BANK/CASH account (e.g. Bank Victoria).
        $balances = [];
        $depositos = [];
        $total = 0;
        $totalDeposito = 0;

        foreach ($all as $b) {
            if ($b->type === 'DEPOSITO') {
                $depositos[] = $b;
                $totalDeposito += (int) $b->balance;
            } else {
                $balances[] = $b;
                $total += (int) $b->balance;
            }
        }

        return view('admin.cash.balances.index', compact(
            'balances', 'depositos', 'total', 'totalDeposito'
        ));
    }
}
