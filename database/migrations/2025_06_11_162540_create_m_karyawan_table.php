<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_karyawan', function (Blueprint $table) {
            $table->string('Kar_ID', 10)->primary(); // Jadikan Kar_ID sebagai primary key
            $table->string('Kar_Nama', 100);
            // Tambahkan kolom lain yang relevan di sini
            // $table->string('Kar_Alamat')->nullable();
            // $table->string('Kar_Telepon', 20)->nullable();
            $table->timestamps(); // Opsional: untuk created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_karyawan');
    }
};
