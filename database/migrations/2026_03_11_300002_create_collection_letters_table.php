<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            // SP1, SP2, SP3, SOMASI, CESSIE
            $table->string('letter_type', 20);
            $table->string('letter_number', 80);
            $table->date('letter_date');
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'letter_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_letters');
    }
};
