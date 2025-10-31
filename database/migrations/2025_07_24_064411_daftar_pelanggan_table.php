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
        Schema::create('daftarpelanggan', function (Blueprint $table) {
            $table->id(); // Ini akan membuat kolom 'id' sebagai primary key auto-increment
            $table->string('kode')->unique()->nullable(); // Contoh: kode pelanggan, unik
            $table->string('anggota'); // Nama anggota/pelanggan
            $table->text('alamat')->nullable(); // Penting: Gunakan 'text' untuk alamat multi-baris
            $table->string('telp')->nullable();
            $table->string('email')->nullable();
            
            // Kolom-kolom baru, didefinisikan secara berurutan, TANPA ->after()
            $table->string('cara_bayar')->nullable(); 
            $table->integer('lama_bayar')->nullable();
            $table->decimal('potongan', 5, 2)->nullable(); 
            $table->decimal('nominal_plafon', 15, 2)->nullable(); 
            $table->string('status')->default('Aktif'); 
            
            $table->date('tanggal')->nullable(); // Tanggal (sesuai dengan cast di model)
            $table->timestamps(); // Ini akan membuat kolom 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftarpelanggan');
    }
};
