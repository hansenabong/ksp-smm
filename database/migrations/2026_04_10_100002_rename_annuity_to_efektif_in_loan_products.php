<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('loan_products')->where('method', 'ANNUITY')->update(['method' => 'EFEKTIF']);
        DB::table('loan_applications')->where('method', 'ANNUITY')->update(['method' => 'EFEKTIF']);
    }

    public function down(): void
    {
        DB::table('loan_products')->where('method', 'EFEKTIF')->update(['method' => 'ANNUITY']);
        DB::table('loan_applications')->where('method', 'EFEKTIF')->update(['method' => 'ANNUITY']);
    }
};
