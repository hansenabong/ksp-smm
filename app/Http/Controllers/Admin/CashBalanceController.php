<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Cash\CashTransactionService;

class CashBalanceController extends Controller
{
    public function index(CashTransactionService $service)
    {
        $balances = $service->getAllBalances();

        $total = 0;
        foreach ($balances as $b) {
            $total += (int) $b->balance;
        }

        return view('admin.cash.balances.index', compact('balances', 'total'));
    }
}