<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalBerkas;
use App\Models\LegalDocument;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalBerkas::with(['documents', 'creator', 'member'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhereHas('member', fn ($m) => $m->where('name', 'ilike', "%{$search}%"));
            });
        }

        $berkasList = $query->paginate(20)->withQueryString();

        return view('admin.legal.index', compact('berkasList'));
    }

    public function create()
    {
        $members = Member::where('is_active', true)->orderBy('name')->get(['id', 'name', 'member_no']);

        return view('admin.legal.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'member_id'   => ['nullable', 'exists:members,id'],
            'files'       => ['required', 'array', 'min:1'],
            'files.*'     => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:102400'],
        ]);

        $berkas = LegalBerkas::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'member_id'   => $validated['member_id'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('legal/' . $berkas->id, 'public');

            $berkas->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_size'     => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
            ]);
        }

        return redirect()->route('admin.legal.show', $berkas)
            ->with('success', 'Berkas berhasil disimpan dengan ' . count($request->file('files')) . ' file.');
    }

    public function show(LegalBerkas $berkas)
    {
        $berkas->load(['documents', 'creator', 'member']);

        return view('admin.legal.show', compact('berkas'));
    }

    public function addFiles(Request $request, LegalBerkas $berkas)
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:102400'],
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('legal/' . $berkas->id, 'public');

            $berkas->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_size'     => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
            ]);
        }

        return back()->with('success', count($request->file('files')) . ' file berhasil ditambahkan.');
    }

    public function destroyFile(LegalBerkas $berkas, LegalDocument $document)
    {
        abort_unless($document->legal_berkas_id === $berkas->id, 404);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'File berhasil dihapus.');
    }

    public function destroy(LegalBerkas $berkas)
    {
        foreach ($berkas->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $berkas->delete();

        return redirect()->route('admin.legal.index')
            ->with('success', 'Berkas "' . $berkas->title . '" berhasil dihapus.');
    }
}
