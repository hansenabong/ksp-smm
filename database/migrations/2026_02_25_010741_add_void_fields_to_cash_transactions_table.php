<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('cash_transactions', function (Blueprint $table) {
      $table->boolean('is_voided')->default(false)->index();
      $table->string('void_reason')->nullable();
      $table->foreignId('voided_by')->nullable()->constrained('users');
      $table->timestamp('voided_at')->nullable();
    });
  }

  public function down(): void {
    Schema::table('cash_transactions', function (Blueprint $table) {
      $table->dropColumn(['is_voided','void_reason','voided_by','voided_at']);
    });
  }
};
