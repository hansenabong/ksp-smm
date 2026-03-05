<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin KSP',
                'email'    => 'admin@ksp.local',
                'password' => Hash::make('Password123!'),
                'role'     => 'ADMIN',
            ],
            [
                'name'     => 'Manajer KSP',
                'email'    => 'manager@ksp.local',
                'password' => Hash::make('Password123!'),
                'role'     => 'MANAGER',
            ],
            [
                'name'     => 'Bendahara KSP',
                'email'    => 'bendahara@ksp.local',
                'password' => Hash::make('Password123!'),
                'role'     => 'BENDAHARA',
            ],
            [
                'name'     => 'Petugas KSP',
                'email'    => 'petugas@ksp.local',
                'password' => Hash::make('Password123!'),
                'role'     => 'PETUGAS',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(['email' => $data['email']], $data);
            $user->syncRoles([$role]);
        }
    }
}
