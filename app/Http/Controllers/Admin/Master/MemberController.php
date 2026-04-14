<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $q = Member::query()->orderBy('member_no');

        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($w) use ($term) {
                $w->where('member_no', 'ilike', '%' . $term . '%')
                    ->orWhere('name', 'ilike', '%' . $term . '%');
            });
        }

        $members = $q->paginate(15)->withQueryString();

        return view('admin.master.members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.master.members.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_no' => ['required', 'string', 'max:30', 'alpha_num', 'unique:members,member_no'],
            'name' => ['required', 'string', 'max:120'],
            'gender' => ['nullable', 'in:L,P'],
            'ktp_no' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'ktp_expiry' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:BELUM_KAWIN,KAWIN,CERAI_HIDUP,CERAI_MATI'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Member::create([
            'member_no' => strtoupper($data['member_no']),
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'ktp_no' => $data['ktp_no'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'ktp_expiry' => $data['ktp_expiry'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.master.members.index')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit(Member $member)
    {
        return view('admin.master.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'member_no' => ['required', 'string', 'max:30', 'alpha_num', Rule::unique('members', 'member_no')->ignore($member->id)],
            'name' => ['required', 'string', 'max:120'],
            'gender' => ['nullable', 'in:L,P'],
            'ktp_no' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'ktp_expiry' => ['nullable', 'date'],
            'marital_status' => ['nullable', 'in:BELUM_KAWIN,KAWIN,CERAI_HIDUP,CERAI_MATI'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $member->update([
            'member_no' => strtoupper($data['member_no']),
            'name' => $data['name'],
            'gender' => $data['gender'] ?? null,
            'ktp_no' => $data['ktp_no'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'ktp_expiry' => $data['ktp_expiry'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? $member->is_active),
        ]);

        return redirect()->route('admin.master.members.index')->with('success', 'Anggota berhasil diupdate.');
    }

    public function toggle(Member $member)
    {
        $member->update(['is_active' => ! $member->is_active]);

        return redirect()->back()->with('success', 'Status anggota berhasil diubah.');
    }
}
