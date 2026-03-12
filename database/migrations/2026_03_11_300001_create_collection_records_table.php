<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->unique()->constrained('loans')->cascadeOnDelete();
            $table->foreignId('petugas_id')->nullable()->constrained('users')->nullOnDelete();
            // 0=normal, 1=SP1, 2=SP2, 3=SP3, 4=Somasi, 5=Cessie
            $table->tinyInteger('escalation_level')->default(0);
            $table->boolean('is_restruk')->default(false);
            $table->date('last_contact_date')->nullable();
            $table->date('next_contact_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_records');
    }
};
