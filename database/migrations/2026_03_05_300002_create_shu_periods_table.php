<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shu_periods', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->unique();
            $table->date('date_from');
            $table->date('date_to');

            // Gross SHU (auto-computed: REVENUE - EXPENSE for the period)
            $table->bigInteger('shu_bruto')->default(0);

            // Distribution percentages stored as basis points (10 000 bp = 100%)
            // Must sum to 10 000
            $table->unsignedSmallInteger('cadangan_umum_bp')->default(2000);  // 20%
            $table->unsignedSmallInteger('jasa_simpanan_bp')->default(3000);  // 30%
            $table->unsignedSmallInteger('jasa_usaha_bp')->default(2500);     // 25%
            $table->unsignedSmallInteger('pengurus_bp')->default(750);        // 7.5%
            $table->unsignedSmallInteger('pegawai_bp')->default(500);         // 5%
            $table->unsignedSmallInteger('sosial_bp')->default(125);          // 1.25%
            $table->unsignedSmallInteger('pendidikan_bp')->default(125);      // 1.25%

            // Derived amounts (bp / 10000 * shu_bruto)
            $table->bigInteger('cadangan_umum_amount')->default(0);
            $table->bigInteger('jasa_simpanan_amount')->default(0);
            $table->bigInteger('jasa_usaha_amount')->default(0);
            $table->bigInteger('pengurus_amount')->default(0);
            $table->bigInteger('pegawai_amount')->default(0);
            $table->bigInteger('sosial_amount')->default(0);
            $table->bigInteger('pendidikan_amount')->default(0);

            $table->string('status')->default('DRAFT'); // DRAFT | FINALIZED
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_periods');
    }
};
