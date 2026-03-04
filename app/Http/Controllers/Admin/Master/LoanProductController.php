<?php

namespace App\Http\Controllers\Admin\Master;
use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoanProductController extends Controller
{
    private array $methods = ['FLAT', 'ANNUITY'];

    public function index(Request $request)
    {
        $q = LoanProduct::query()->orderBy('name');

        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        if ($request->filled('method')) {
            $q->where('method', $request->method);
        }

        if ($request->filled('q')) {
            $q->where('name', 'ilike', '%'.$request->q.'%');
        }

        $loanProducts = $q->paginate(15)->withQueryString();
        $methods = $this->methods;

        return view('admin.master.loan_products.index', compact('loanProducts', 'methods'));
    }

    public function create()
    {
        $methods = $this->methods;
        return view('admin.master.loan_products.create', compact('methods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:loan_products,name'],
            'method' => ['required', Rule::in($this->methods)],
            'interest_rate_monthly_bp' => ['required', 'integer', 'min:0', 'max:100000'], // 1000% max (aman aja)
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'max_tenor_months' => ['required', 'integer', 'min:1', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        LoanProduct::create([
            'name' => $data['name'],
            'method' => $data['method'],
            'interest_rate_monthly_bp' => (int)$data['interest_rate_monthly_bp'],
            'admin_fee' => (int)($data['admin_fee'] ?? 0),
            'max_tenor_months' => (int)$data['max_tenor_months'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.master.loan_products.index')
            ->with('success', 'Jenis pinjaman berhasil ditambahkan.');
    }

    public function edit(LoanProduct $loanProduct)
    {
        $methods = $this->methods;
        return view('admin.master.loan_products.edit', compact('loanProduct', 'methods'));
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('loan_products', 'name')->ignore($loanProduct->id)],
            'method' => ['required', Rule::in($this->methods)],
            'interest_rate_monthly_bp' => ['required', 'integer', 'min:0', 'max:100000'],
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'max_tenor_months' => ['required', 'integer', 'min:1', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $loanProduct->update([
            'name' => $data['name'],
            'method' => $data['method'],
            'interest_rate_monthly_bp' => (int)$data['interest_rate_monthly_bp'],
            'admin_fee' => (int)($data['admin_fee'] ?? 0),
            'max_tenor_months' => (int)$data['max_tenor_months'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.master.loan_products.index')
            ->with('success', 'Jenis pinjaman berhasil diupdate.');
    }

    public function toggle(LoanProduct $loanProduct)
    {
        $loanProduct->update(['is_active' => ! $loanProduct->is_active]);

        return redirect()->back()->with('success', 'Status jenis pinjaman berhasil diubah.');
    }
}