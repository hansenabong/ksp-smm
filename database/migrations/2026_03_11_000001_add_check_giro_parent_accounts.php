<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add parent COA accounts for CHECK (Cek) and GIRO (Bilyet/Giro) cash account types.
 * These sit at the same level as 1110 (Kas) and 1120 (Bank).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Find the parent of account 1110 (Kas) — we'll use the same parent for 1140 and 1150
        $kas = DB::table('accounts')->where('code', '1110')->first();
        if (! $kas) {
            return; // AccountingSeeder not run yet — skip
        }
        $parentId = $kas->parent_id;

        $now = now();

        // 1140 — Cek (CHECK instruments received)
        if (! DB::table('accounts')->where('code', '1140')->exists()) {
            DB::table('accounts')->insert([
                'code'           => '1140',
                'name'           => 'Cek',
                'type'           => 'ASSET',
                'normal_balance' => 'D',
                'parent_id'      => $parentId,
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // 1150 — Bilyet / Giro
        if (! DB::table('accounts')->where('code', '1150')->exists()) {
            DB::table('accounts')->insert([
                'code'           => '1150',
                'name'           => 'Bilyet / Giro',
                'type'           => 'ASSET',
                'normal_balance' => 'D',
                'parent_id'      => $parentId,
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('accounts')->whereIn('code', ['1140', '1150'])->delete();
    }
};
