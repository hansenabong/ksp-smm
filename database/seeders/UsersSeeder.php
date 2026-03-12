<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Test accounts for KSP system.
 *
 * Passwords follow the pattern: <Role>@KSP2025
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

            // ADMIN
            [
                'name'     => 'Admin Utama',
                'email'    => 'admin@ksp.local',
                'password' => Hash::make('Admin@KSP2025'),
                'role'     => 'ADMIN',
            ],
            [
                'name'     => 'Admin Sistem',
                'email'    => 'admin2@ksp.local',
                'password' => Hash::make('Admin2@KSP2025'),
                'role'     => 'ADMIN',
            ],

            // MANAGER
            [
                'name'     => 'Manajer Operasional',
                'email'    => 'manager@ksp.local',
                'password' => Hash::make('Manager@KSP2025'),
                'role'     => 'MANAGER',
            ],
            [
                'name'     => 'Manajer Kredit',
                'email'    => 'manager2@ksp.local',
                'password' => Hash::make('Manager2@KSP2025'),
                'role'     => 'MANAGER',
            ],

            // BENDAHARA
            [
                'name'     => 'Bendahara Umum',
                'email'    => 'bendahara@ksp.local',
                'password' => Hash::make('Bendahara@KSP2025'),
                'role'     => 'BENDAHARA',
            ],
            [
                'name'     => 'Bendahara Kas',
                'email'    => 'bendahara2@ksp.local',
                'password' => Hash::make('Bendahara2@KSP2025'),
                'role'     => 'BENDAHARA',
            ],

            // PETUGAS
            [
                'name'     => 'Petugas Teller 1',
                'email'    => 'petugas@ksp.local',
                'password' => Hash::make('Petugas@KSP2025'),
                'role'     => 'PETUGAS',
            ],
            [
                'name'     => 'Petugas Teller 2',
                'email'    => 'petugas2@ksp.local',
                'password' => Hash::make('Petugas2@KSP2025'),
                'role'     => 'PETUGAS',
            ],
            [
                'name'     => 'Petugas Lapangan',
                'email'    => 'petugas3@ksp.local',
                'password' => Hash::make('Petugas3@KSP2025'),
                'role'     => 'PETUGAS',
            ],

            // KASIR — front-desk teller: cash in/out, savings, loan payments
            [
                'name'     => 'Kasir Teller',
                'email'    => 'kasir@ksp.local',
                'password' => Hash::make('Kasir@KSP2025'),
                'role'     => 'KASIR',
            ],

            // ADMIN_DATA — data entry: member registration, loan applications
            [
                'name'     => 'Admin Data',
                'email'    => 'admindata@ksp.local',
                'password' => Hash::make('AdminData@KSP2025'),
                'role'     => 'ADMIN_DATA',
            ],

            // ADMIN_COLLECTION — debt collector: track & receive loan payments
            [
                'name'     => 'Admin Penagihan',
                'email'    => 'admincollection@ksp.local',
                'password' => Hash::make('AdminCollection@KSP2025'),
                'role'     => 'ADMIN_COLLECTION',
            ],

            // ── Real team accounts (for UAT / live testing) ───────────────────

            // KASIR (Finance) — front-desk cash entry, savings, loan payments
            [
                'name'     => 'Dilla',
                'email'    => 'dilla@ksp.local',
                'password' => Hash::make('Dilla@KSP2025'),
                'role'     => 'KASIR',
            ],
            [
                'name'     => 'Nadia',
                'email'    => 'nadia@ksp.local',
                'password' => Hash::make('Nadia@KSP2025'),
                'role'     => 'KASIR',
            ],

            // ADMIN_DATA — member & loan application data entry
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

            // BENDAHARA (Treasury) — full transaction entry, approve/void
            [
                'name'     => 'Fitri',
                'email'    => 'fitri@ksp.local',
                'password' => Hash::make('Fitri@KSP2025'),
                'role'     => 'BENDAHARA',
            ],

            // PENGAWAS — read-only supervisor (admin data, collection, treasury)
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
