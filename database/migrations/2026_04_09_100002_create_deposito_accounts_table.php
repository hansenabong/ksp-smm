<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposito_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code', 40)->unique();

            $table->foreignId('member_id')->constrained('members');
            $table->foreignId('saving_product_id')->constrained('saving_products');

            $table->bigInteger('principal_amount');
            // Locked at opening — independent of product defaults afterwards
            $table->decimal('interest_rate_pct', 5, 2);
            $table->unsignedSmallInteger('term_months');

            $table->date('start_date');
            $table->date('maturity_date');

            // ACTIVE | MATURED | WITHDRAWN | CLOSED
            $table->string('status', 12)->default('ACTIVE');

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['member_id', 'saving_product_id']);
            $table->index(['status', 'maturity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposito_accounts');
    }
};
