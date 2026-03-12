<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_records', function (Blueprint $table) {
            $table->unsignedBigInteger('njop')->nullable()->after('branch');
            $table->unsignedInteger('luas_tanah')->nullable()->after('njop');     // m²
            $table->unsignedInteger('luas_bangunan')->nullable()->after('luas_tanah'); // m²
            $table->text('alamat_tinggal')->nullable()->after('luas_bangunan');   // current residence
            $table->string('wilayah', 100)->nullable()->after('alamat_tinggal'); // area/city
        });
    }

    public function down(): void
    {
        Schema::table('collection_records', function (Blueprint $table) {
            $table->dropColumn(['njop', 'luas_tanah', 'luas_bangunan', 'alamat_tinggal', 'wilayah']);
        });
    }
};
