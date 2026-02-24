<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'super@ksp.local'],
            ['name' => 'Super Admin', 'password' => bcrypt('Password123!')]
        );

        $user->syncRoles(['SUPER_ADMIN']);
    }
}