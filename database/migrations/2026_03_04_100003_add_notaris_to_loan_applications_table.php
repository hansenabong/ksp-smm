<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->string('notaris_name', 150)->nullable()->after('form_data');
            $table->string('notaris_deed_number', 60)->nullable()->after('notaris_name');
            $table->date('notaris_deed_date')->nullable()->after('notaris_deed_number');
            $table->string('notaris_city', 100)->nullable()->after('notaris_deed_date');
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['notaris_name', 'notaris_deed_number', 'notaris_deed_date', 'notaris_city']);
        });
    }
};
