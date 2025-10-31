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
        // Menggunakan nama tabel standar Laravel (plural snake_case)
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->id();
            // Pastikan tabel 'daftarpelanggan' ada sebelum migrasi ini dijalankan
            $table->foreignId('pelanggan_id')->nullable()->constrained('daftarpelanggan')->onDelete('set null');
            $table->string('no_order')->unique();
            $table->string('po_pelanggan')->nullable();
            $table->date('tgl_kirim')->nullable();
            $table->decimal('bruto', 15, 2)->default(0);
            $table->decimal('disc', 5, 2)->default(0); // Mengubah presisi disc
            $table->decimal('pajak', 15, 2)->default(0);
            $table->decimal('netto', 15, 2)->default(0);
            $table->string('status')->default('Draft'); // Menambahkan kolom status
            $table->date('tanggal_pesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_orders');
    }
};
