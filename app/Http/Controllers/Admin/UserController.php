<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /** Roles visible to SUPER_ADMIN */
    private const ALL_ROLES = ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'BENDAHARA', 'PETUGAS'];

    /** Roles an ADMIN is allowed to assign (cannot create/promote to SUPER_ADMIN or ADMIN) */
    private const ADMIN_ASSIGNABLE = ['MANAGER', 'BENDAHARA', 'PETUGAS'];

    private function allowedRoles(): array
    {
        return auth()->user()->hasRole('SUPER_ADMIN')
            ? self::ALL_ROLES
            : self::ADMIN_ASSIGNABLE;
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
            'roles' => $this->allowedRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $allowed = $this->allowedRoles();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in($allowed)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user'  => $user->load('roles'),
            'roles' => $this->allowedRoles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $allowed = $this->allowedRoles();

        $rules = [
            'name'  => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in($allowed)],
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

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
