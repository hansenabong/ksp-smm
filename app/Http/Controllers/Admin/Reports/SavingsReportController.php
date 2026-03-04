<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Models\SavingTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SavingsReportController extends Controller
{
    public function index(Request $request)
    {
        $memberId = $request->input('member_id');

        $members = Member::orderBy('member_no')->get();
        $products = SavingProduct::orderBy('name')->get();

        // filter transaksi aktif saja
        $txQ = SavingTransaction::query()
            ->where(function ($q) {
                $q->whereNull('is_voided')->orWhere('is_voided', false);
            });

        if ($memberId) {
            $txQ->where('member_id', $memberId);
        }

        $rows = (clone $txQ)
            ->selectRaw('member_id, saving_product_id,
                SUM(CASE WHEN type = \'DEPOSIT\' THEN amount ELSE 0 END) AS total_deposit,
                SUM(CASE WHEN type = \'WITHDRAW\' THEN amount ELSE 0 END) AS total_withdraw')
            ->groupBy('member_id', 'saving_product_id')
            ->get();

        // build matrix [member][product] = balance
        $matrix = [];
        foreach ($rows as $r) {
            $bal = (int)$r->total_deposit - (int)$r->total_withdraw;
            $matrix[$r->member_id][$r->saving_product_id] = [
                'deposit' => (int)$r->total_deposit,
                'withdraw' => (int)$r->total_withdraw,
                'balance' => $bal,
            ];
        }

        return view('admin.reports.savings.index', compact('members', 'products', 'matrix', 'memberId'));
    }
    public function export(Request $request): StreamedResponse
    {
        $memberId = $request->input('member_id');

        $membersQ = Member::query()->orderBy('member_no');
        if ($memberId) $membersQ->where('id', $memberId);
        $members = $membersQ->get();

        $products = SavingProduct::orderBy('name')->get();

        $txQ = SavingTransaction::query()
            ->where(function ($q) {
                $q->whereNull('is_voided')->orWhere('is_voided', false);
            });

        if ($memberId) $txQ->where('member_id', $memberId);

        $rows = (clone $txQ)
            ->selectRaw("member_id, saving_product_id,
            SUM(CASE WHEN type='DEPOSIT' THEN amount ELSE 0 END) AS total_deposit,
            SUM(CASE WHEN type='WITHDRAW' THEN amount ELSE 0 END) AS total_withdraw")
            ->groupBy('member_id', 'saving_product_id')
            ->get();

        $matrix = [];
        foreach ($rows as $r) {
            $bal = (int)$r->total_deposit - (int)$r->total_withdraw;
            $matrix[$r->member_id][$r->saving_product_id] = $bal;
        }

        $filename = $memberId ? "report_savings_member_{$memberId}.csv" : "report_savings_all.csv";

        return response()->streamDownload(function () use ($members, $products, $matrix) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            // Header dinamis: product columns
            $header = ['member_no', 'member_name'];
            foreach ($products as $p) $header[] = $p->name;
            $header[] = 'total';
            fputcsv($out, $header, ';');

            foreach ($members as $m) {
                $row = [$m->member_no, $m->name];
                $total = 0;

                foreach ($products as $p) {
                    $bal = (int)($matrix[$m->id][$p->id] ?? 0);
                    $row[] = $bal;
                    $total += $bal;
                }

                $row[] = $total;
                fputcsv($out, $row, ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
