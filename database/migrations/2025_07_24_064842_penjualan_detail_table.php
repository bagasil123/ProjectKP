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
        // Menggunakan nama tabel jamak (plural) sesuai konvensi
        Schema::create('penjualan_details', function (Blueprint $table) {
            $table->id();
            
            // PERBAIKAN: Memastikan foreign key merujuk ke tabel 'penjualans' yang benar
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            
            // Pastikan tabel 'products' ada
            $table->unsignedBigInteger('product_id'); // Foreign key dinonaktifkan sementara
            
            $table->decimal('qty', 10, 2);
            $table->string('satuan');
            $table->decimal('harga', 15, 2);
            $table->decimal('disc', 5, 2)->default(0);
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('nominal', 15, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_details');
    }
};
