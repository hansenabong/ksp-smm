<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;

class MemberSavingsController extends Controller
{
    public function index()
    {
        // Sum balance per member per product (non-voided)
        $balanceRows = SavingTransaction::notVoided()
            ->selectRaw("member_id, saving_product_id,
                SUM(CASE WHEN type = 'DEPOSIT' THEN amount ELSE -amount END) AS balance")
            ->groupBy('member_id', 'saving_product_id')
            ->get();

        // Pivot: [member_id => [product_id => balance]]
        $balanceMap = [];
        foreach ($balanceRows as $row) {
            $balanceMap[$row->member_id][$row->saving_product_id] = (int)$row->balance;
        }

        $members  = Member::orderBy('member_no')->get();
        $products = SavingProduct::orderBy('name')->get();

        // Total per member (all products)
        $memberTotals = [];
        foreach ($balanceMap as $memberId => $productBalances) {
            $memberTotals[$memberId] = array_sum($productBalances);
        }

        return view('admin.savings.member_balance.index', compact(
            'members', 'products', 'balanceMap', 'memberTotals'
        ));
    }

    public function show(Member $member)
    {
        $member->load('savingTransactions.savingProduct', 'savingTransactions.cashAccount');

        $products = SavingProduct::orderBy('name')->get();

        // Transactions grouped by product
        $txByProduct = $member->savingTransactions
            ->where('is_voided', false)
            ->sortBy('trx_date')
            ->groupBy('saving_product_id');

        // Running balance per product
        $balances = [];
        foreach ($products as $product) {
            $txs = $txByProduct->get($product->id, collect());
            $running = 0;
            $rows    = [];
            foreach ($txs as $tx) {
                $delta   = $tx->type === 'DEPOSIT' ? (int)$tx->amount : -(int)$tx->amount;
                $running += $delta;
                $rows[]  = ['tx' => $tx, 'balance' => $running];
            }
            $balances[$product->id] = [
                'product'  => $product,
                'rows'     => $rows,
                'balance'  => $running,
            ];
        }

        $grandTotal = array_sum(array_column($balances, 'balance'));

        return view('admin.savings.member_balance.show', compact(
            'member', 'products', 'balances', 'grandTotal'
        ));
    }
}
