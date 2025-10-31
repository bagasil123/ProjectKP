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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_jualan')->unique();
            
            // PERBAIKAN: Merujuk ke tabel 'customer_orders' yang benar
            $table->foreignId('customer_order_id')->constrained('customer_orders')->onDelete('cascade');
            
            // PERBAIKAN: Merujuk ke tabel 'daftarpelanggan' yang benar
            $table->foreignId('pelanggan_id')->constrained('daftarpelanggan')->onDelete('cascade');
            
            $table->date('tgl_kirim');
            $table->date('jatuh_tempo')->nullable();
            $table->string('po_pelanggan')->nullable();
            $table->decimal('bruto', 15, 2);
            $table->decimal('total_disc', 15, 2);
            $table->decimal('total_pajak', 15, 2);
            $table->decimal('netto', 15, 2);
            $table->string('pengguna');
            $table->string('status')->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
