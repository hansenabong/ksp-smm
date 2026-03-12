<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** All roles — SUPER_ADMIN can assign any */
    private const ALL_ROLES = [
        'SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA', 'PETUGAS',
        'KASIR', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'PENGAWAS', 'MEMBER',
    ];

    /** ADMIN may create/assign operational roles (not SUPER_ADMIN/ADMIN) */
    private const ADMIN_ASSIGNABLE = ['MANAGER', 'BENDAHARA', 'PETUGAS', 'KASIR', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'PENGAWAS', 'MEMBER'];

    private function allowedRoles(): array
    {
        return auth()->user()->hasRole('SUPER_ADMIN')
            ? self::ALL_ROLES
            : self::ADMIN_ASSIGNABLE;
    }

    /**
     * Guard: ADMIN cannot edit/delete users with roles outside their allowed set.
     * Returns true if the acting user is allowed to manage the target user.
     */
    private function canManage(User $target): bool
    {
        if (auth()->user()->hasRole('SUPER_ADMIN')) {
            return true;
        }
        // ADMIN may only manage users whose current role is in ADMIN_ASSIGNABLE
        $targetRoles = $target->roles->pluck('name')->toArray();
        return empty(array_diff($targetRoles, self::ADMIN_ASSIGNABLE));
    }

    public function index(Request $request)
    {
        $q = User::with('roles')->orderBy('name');

        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($w) use ($term) {
                $w->where('name', 'ilike', '%'.$term.'%')
                  ->orWhere('email', 'ilike', '%'.$term.'%');
            });
        }

        if ($request->filled('role')) {
            $q->whereHas('roles', fn($r) => $r->where('name', $request->role));
        }

        $users = $q->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'users'    => $users,
            'allRoles' => self::ALL_ROLES,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles'   => $this->allowedRoles(),
            'members' => Member::active()->orderBy('member_no')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $allowed = $this->allowedRoles();

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in($allowed)],
            'member_id' => ['nullable', 'exists:members,id'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        // Link member account for portal access
        if (! empty($data['member_id'])) {
            Member::where('id', $data['member_id'])->update(['user_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        if (! $this->canManage($user)) {
            abort(403, 'Anda tidak berwenang mengelola pengguna ini.');
        }

        $linkedMember = Member::where('user_id', $user->id)->first();
        return view('admin.users.edit', [
            'user'          => $user->load('roles'),
            'roles'         => $this->allowedRoles(),
            'members'       => Member::active()->orderBy('member_no')->get(),
            'linkedMember'  => $linkedMember,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (! $this->canManage($user)) {
            abort(403, 'Anda tidak berwenang mengelola pengguna ini.');
        }

        $allowed = $this->allowedRoles();

        $rules = [
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in($allowed)],
            'member_id' => ['nullable', 'exists:members,id'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        // Update member link: remove old link, set new link
        Member::where('user_id', $user->id)->update(['user_id' => null]);
        if (! empty($data['member_id'])) {
            Member::where('id', $data['member_id'])->update(['user_id' => $user->id]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if (! $this->canManage($user)) {
            abort(403, 'Anda tidak berwenang menghapus pengguna ini.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
