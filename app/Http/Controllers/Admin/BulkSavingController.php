<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Member;
use App\Models\SavingProduct;
use App\Services\Savings\SavingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkSavingController extends Controller
{
    public function create()
    {
        $savingProducts = SavingProduct::active()->orderBy('name')->get();
        $cashAccounts   = CashAccount::where('is_active', true)->orderBy('name')->get();
        $members        = Member::active()->orderBy('member_no')->get();

        return view('admin.savings.bulk_wajib.create', compact('savingProducts', 'cashAccounts', 'members'));
    }

    public function store(Request $request, SavingsService $service)
    {
        $request->validate([
            'trx_date'        => ['required', 'date'],
            'saving_product_id' => ['required', 'exists:saving_products,id'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'amounts'         => ['required', 'array', 'min:1'],
            'amounts.*'       => ['nullable', 'integer', 'min:0'],
        ]);

        $date      = $request->trx_date;
        $productId = $request->saving_product_id;
        $accountId = $request->cash_account_id;
        $amounts   = $request->input('amounts', []);

        // Filter to members with amount > 0
        $entries = collect($amounts)
            ->filter(fn($amt) => $amt !== null && (int)$amt > 0)
            ->map(fn($amt, $memberId) => ['member_id' => (int)$memberId, 'amount' => (int)$amt]);

        if ($entries->isEmpty()) {
            return back()->withInput()->withErrors(['amounts' => 'Masukkan minimal satu jumlah setoran.']);
        }

        // Validate all member IDs exist and are active
        $validMemberIds = Member::active()->whereIn('id', $entries->pluck('member_id'))->pluck('id');
        $invalidCount   = $entries->count() - $validMemberIds->count();
        if ($invalidCount > 0) {
            return back()->withInput()->withErrors(['amounts' => "Ditemukan {$invalidCount} anggota tidak valid."]);
        }

        DB::transaction(function () use ($entries, $date, $productId, $accountId, $service) {
            foreach ($entries as $entry) {
                $service->deposit([
                    'trx_date'         => $date,
                    'member_id'        => $entry['member_id'],
                    'saving_product_id'=> $productId,
                    'cash_account_id'  => $accountId,
                    'amount'           => $entry['amount'],
                    'description'      => 'Simpanan Wajib Bulanan',
                ], auth()->id());
            }
        });

        $count = $entries->count();
        $total = $entries->sum('amount');

        return redirect()->route('admin.savings.deposit.index')
            ->with('success', "Berhasil menyimpan {$count} setoran simpanan wajib. Total: Rp " . number_format($total, 0, ',', '.'));
    }
}
