<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add SHU distribution leaf accounts under 3200 (Sisa Hasil Usaha)
        $shu3200 = DB::table('accounts')->where('code', '3200')->first();

        if (!$shu3200) {
            return; // AccountingSeeder has not been run yet; safe to skip
        }

        $accounts = [
            ['3213', 'Dana Pengurus',    'EQUITY', 'C'],
            ['3214', 'Dana Pegawai',     'EQUITY', 'C'],
            ['3215', 'Dana Sosial',      'EQUITY', 'C'],
            ['3216', 'Dana Pendidikan',  'EQUITY', 'C'],
        ];

        foreach ($accounts as [$code, $name, $type, $normal]) {
            $exists = DB::table('accounts')->where('code', $code)->exists();
            if (!$exists) {
                DB::table('accounts')->insert([
                    'code'           => $code,
                    'name'           => $name,
                    'type'           => $type,
                    'normal_balance' => $normal,
                    'parent_id'      => $shu3200->id,
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('accounts')->whereIn('code', ['3213', '3214', '3215', '3216'])->delete();
    }
};
