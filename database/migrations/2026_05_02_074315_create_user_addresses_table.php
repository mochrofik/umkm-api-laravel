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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name'); // Nama Penerima
            $table->string('phone_number'); // Nomor Telepon Penerima
            $table->string('province'); // Provinsi
            $table->string('city'); // Kabupaten/Kota
            $table->string('district'); // Kecamatan
            $table->string('subdistrict'); // Kelurahan/Desa
            $table->string('postal_code', 5); // Kode Pos
            $table->string('address_details', 150); // Alamat Lengkap (Jalan, No Rumah, dll)
            $table->string('label')->nullable(); // Label (Rumah, Kantor, dll)
            $table->boolean('is_primary')->default(false); // Alamat Utama
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
