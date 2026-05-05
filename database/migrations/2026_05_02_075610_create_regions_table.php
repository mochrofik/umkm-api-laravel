<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode wilayah (contoh: 31 untuk DKI Jakarta, 31.71 untuk Jakarta Pusat)
            $table->string('parent_code')->nullable()->index(); // Kode induknya
            $table->string('name'); // Nama wilayah
            $table->tinyInteger('level'); // 1: Provinsi, 2: Kabupaten/Kota, 3: Kecamatan, 4: Kelurahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
