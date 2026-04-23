<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add parent COA account 1160 "Penempatan Deposito" for the DEPOSITO cash account type.
 * Sits at the same level as 1110 (Kas), 1120 (Bank), 1140 (Cek), 1150 (Bilyet/Giro).
 *
 * DEPOSITO-type cash accounts represent parked funds (not liquid cash) — they are
 * shown in the Cash Balance list as a record but NOT added to Total Saldo Kas.
 * When the deposito matures, a TRANSFER moves the funds to Bank Victoria/BCA,
 * which then flows into Total Saldo Kas automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        $kas = DB::table('accounts')->where('code', '1110')->first();
        if (! $kas) {
            return; // AccountingSeeder not run yet — skip
        }
        $parentId = $kas->parent_id;

        if (! DB::table('accounts')->where('code', '1160')->exists()) {
            $now = now();
            DB::table('accounts')->insert([
                'code'           => '1160',
                'name'           => 'Penempatan Deposito',
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
        DB::table('accounts')->where('code', '1160')->delete();
    }
};
