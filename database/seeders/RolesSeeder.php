<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'SUPER_ADMIN', 'ADMIN', 'BENDAHARA', 'MANAGER', 'PETUGAS',
            'KASIR', 'ADMIN_DATA', 'ADMIN_COLLECTION', 'PENGAWAS', 'MEMBER',
        ] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}