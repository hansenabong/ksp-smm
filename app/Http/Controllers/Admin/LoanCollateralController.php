<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\LoanCollateral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoanCollateralController extends Controller
{
    /** List all collateral for a loan application. */
    public function index(LoanApplication $application)
    {
        $collaterals = $application->collaterals()->with(['creator', 'releasedBy'])->orderBy('created_at')->get();
        return view('admin.collaterals.index', compact('application', 'collaterals'));
    }

    public function create(LoanApplication $application)
    {
        $types = LoanCollateral::types();
        return view('admin.collaterals.create', compact('application', 'types'));
    }

    public function store(Request $request, LoanApplication $application)
    {
        $data = $request->validate([
            'type'            => ['required', 'in:SERTIPIKAT,BPKB,DEPOSITO,LAINNYA'],
            'description'     => ['nullable', 'string', 'max:500'],
            'estimated_value' => ['required', 'integer', 'min:0'],
            'document'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = null;
        $name = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('collaterals', 'private');
            $name = $file->getClientOriginalName();
        }

        $application->collaterals()->create([
            'type'            => $data['type'],
            'description'     => $data['description'] ?? null,
            'estimated_value' => $data['estimated_value'],
            'document_path'   => $path,
            'document_name'   => $name,
            'status'          => 'HELD',
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('admin.loans.applications.collaterals.index', $application)
            ->with('success', 'Agunan berhasil ditambahkan.');
    }

    /** Download collateral document. */
    public function download(LoanApplication $application, LoanCollateral $collateral)
    {
        abort_if($collateral->loan_application_id !== $application->id, 404);
        abort_if(! $collateral->document_path || ! Storage::disk('private')->exists($collateral->document_path), 404);

        return Storage::disk('private')->download($collateral->document_path, $collateral->document_name ?? 'document');
    }

    /** Show release form. */
    public function releaseForm(LoanApplication $application, LoanCollateral $collateral)
    {
        abort_if($collateral->loan_application_id !== $application->id, 404);
        abort_if($collateral->status === 'RELEASED', 422, 'Agunan sudah dilepas.');
        return view('admin.collaterals.release', compact('application', 'collateral'));
    }

    /** Mark collateral as released. */
    public function release(Request $request, LoanApplication $application, LoanCollateral $collateral)
    {
        abort_if($collateral->loan_application_id !== $application->id, 404);
        abort_if($collateral->status === 'RELEASED', 422, 'Agunan sudah dilepas.');

        $data = $request->validate([
            'release_note' => ['required', 'string', 'max:500'],
            'released_at'  => ['required', 'date'],
        ]);

        $collateral->update([
            'status'       => 'RELEASED',
            'released_at'  => $data['released_at'],
            'released_by'  => auth()->id(),
            'release_note' => $data['release_note'],
        ]);

        return redirect()->route('admin.loans.applications.collaterals.index', $application)
            ->with('success', 'Agunan berhasil dilepas / dikembalikan.');
    }

    public function destroy(LoanApplication $application, LoanCollateral $collateral)
    {
        abort_if($collateral->loan_application_id !== $application->id, 404);
        abort_if($collateral->status === 'RELEASED', 422, 'Agunan yang sudah dilepas tidak dapat dihapus.');

        if ($collateral->document_path) {
            Storage::disk('private')->delete($collateral->document_path);
        }
        $collateral->delete();

        return redirect()->route('admin.loans.applications.collaterals.index', $application)
            ->with('success', 'Agunan berhasil dihapus.');
    }
}
