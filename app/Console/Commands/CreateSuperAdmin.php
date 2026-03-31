<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'ksp:create-superadmin';

    protected $description = 'Create a Super Admin user for the KSP system';

    public function handle(): int
    {
        $this->info('=== Buat Akun Super Admin KSP ===');
        $this->newLine();

        $name = $this->ask('Nama lengkap');

        $email = $this->ask('Email');
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            $this->error('Email tidak valid atau sudah digunakan: ' . $validator->errors()->first('email'));
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 karakter)');
        if (strlen($password) < 8) {
            $this->error('Password harus minimal 8 karakter.');
            return self::FAILURE;
        }

        $confirm = $this->secret('Konfirmasi password');
        if ($password !== $confirm) {
            $this->error('Konfirmasi password tidak cocok.');
            return self::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('SUPER_ADMIN');

        $this->newLine();
        $this->info('Super Admin berhasil dibuat!');
        $this->table(
            ['Nama', 'Email', 'Role'],
            [[$user->name, $user->email, 'SUPER_ADMIN']]
        );

        return self::SUCCESS;
    }
}
