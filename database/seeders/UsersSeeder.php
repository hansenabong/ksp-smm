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
