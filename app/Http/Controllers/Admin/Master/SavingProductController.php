<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\SavingProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavingProductController extends Controller
{
    public function index(Request $request)
    {
        $q = SavingProduct::query()->orderBy('name');

        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        if ($request->filled('q')) {
            $q->where('name', 'ilike', '%' . $request->q . '%');
        }

        $savingProducts = $q->paginate(15)->withQueryString();

        return view('admin.master.saving_products.index', compact('savingProducts'));
    }

    public function create()
    {
        return view('admin.master.saving_products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:saving_products,name'],
            'is_active' => ['nullable', 'boolean'],
            'is_withdrawable' => ['nullable', 'boolean'],
        ]);

        SavingProduct::create([
            'name' => $request->input('name'),
            'is_active' => $request->boolean('is_active'),         // default false kalau tidak ada
            'is_withdrawable' => $request->boolean('is_withdrawable'),
        ]);

        return redirect()->route('admin.master.saving_products.index')
            ->with('success', 'Jenis simpanan berhasil ditambahkan.');
    }

    public function edit(SavingProduct $savingProduct)
    {
        return view('admin.master.saving_products.edit', compact('savingProduct'));
    }

    public function update(Request $request, SavingProduct $savingProduct)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('saving_products', 'name')->ignore($savingProduct->id)],
            'is_active' => ['nullable', 'boolean'],
            'is_withdrawable' => ['nullable', 'boolean'],
        ]);

        $savingProduct->update([
            'name' => $request->input('name'),
            'is_active' => $request->boolean('is_active'),
            'is_withdrawable' => $request->boolean('is_withdrawable'),
        ]);

        return redirect()->route('admin.master.saving_products.index')
            ->with('success', 'Jenis simpanan berhasil diupdate.');
    }

    public function toggle(SavingProduct $savingProduct)
    {
        $savingProduct->update(['is_active' => ! $savingProduct->is_active]);

        return redirect()->back()->with('success', 'Status jenis simpanan berhasil diubah.');
    }
}
