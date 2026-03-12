<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Team accounts for KSP system.
 *
 * Roles: SUPER_ADMIN, ADMIN_DATA, ADMIN_COLLECTION, TREASURY, FINANCE, PENGAWAS
 * Run: php artisan db:seed --class=UsersSeeder
 */
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // SUPER_ADMIN
            [
                'name'     => 'Super Administrator',
                'email'    => 'superadmin@ksp.local',
                'password' => Hash::make('SuperAdmin@KSP2025'),
                'role'     => 'SUPER_ADMIN',
            ],

            // FINANCE — accounting/finance menu only
            [
                'name'     => 'Dilla',
                'email'    => 'dilla@ksp.local',
                'password' => Hash::make('Dilla@KSP2025'),
                'role'     => 'FINANCE',
            ],
            [
                'name'     => 'Nadia',
                'email'    => 'nadia@ksp.local',
                'password' => Hash::make('Nadia@KSP2025'),
                'role'     => 'FINANCE',
            ],

            // ADMIN_DATA — member registration, loan applications
            [
                'name'     => 'Gio',
                'email'    => 'gio@ksp.local',
                'password' => Hash::make('Gio@KSP2025'),
                'role'     => 'ADMIN_DATA',
            ],
            [
                'name'     => 'Adam',
                'email'    => 'adam@ksp.local',
                'password' => Hash::make('Adam@KSP2025'),
                'role'     => 'ADMIN_DATA',
            ],

            // ADMIN_COLLECTION — loan collection & payment monitoring
            [
                'name'     => 'Dia',
                'email'    => 'dia@ksp.local',
                'password' => Hash::make('Dia@KSP2025'),
                'role'     => 'ADMIN_COLLECTION',
            ],
            [
                'name'     => 'Franky',
                'email'    => 'franky@ksp.local',
                'password' => Hash::make('Franky@KSP2025'),
                'role'     => 'ADMIN_COLLECTION',
            ],

            // TREASURY — surat menyurat, PK, OL, permohonan dana, kwitansi
            [
                'name'     => 'Fitri',
                'email'    => 'fitri@ksp.local',
                'password' => Hash::make('Fitri@KSP2025'),
                'role'     => 'TREASURY',
            ],

            // PENGAWAS — read-only supervisor
            [
                'name'     => 'Sri',
                'email'    => 'sri@ksp.local',
                'password' => Hash::make('Sri@KSP2025'),
                'role'     => 'PENGAWAS',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(['email' => $data['email']], $data);

            // Re-run safe: update name + password for existing users
            if (!$user->wasRecentlyCreated) {
                $user->update([
                    'name'     => $data['name'],
                    'password' => $data['password'],
                ]);
            }

            $user->syncRoles([$role]);
        }
    }
}
