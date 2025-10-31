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
        Schema::create('td_slsgtrcv', function (Blueprint $table) {
            // --- Kolom Kunci ---
            $table->id(); // Kunci utama standar Laravel
            $table->unsignedBigInteger('terima_gudang_id'); // Kunci asing ke th_slsgtrcv.id

            // --- Informasi Produk ---
            $table->string('Rcv_ProdCode', 50);
            $table->string('Rcv_prodname', 255)->nullable();
            $table->string('Rcv_uom', 50)->nullable();

            // --- Informasi Kuantitas (Sangat Penting) ---
            $table->decimal('Rcv_Qty_Sent', 15, 2)->default(0);    // Jumlah yang seharusnya dikirim (dari transfer)
            $table->decimal('Rcv_Qty_Received', 15, 2)->default(0); // Jumlah yang benar-benar diterima
            $table->decimal('Rcv_Qty_Rejected', 15, 2)->default(0); // Jumlah yang diterima tapi rusak/ditolak

            // --- Informasi Finansial ---
            $table->decimal('Rcv_cogs', 15, 2)->default(0); // Harga per item (dari transfer)
            $table->decimal('Rcv_subtotal', 15, 2)->default(0); // Total nilai yang diterima (Qty_Received * COGS)

            // --- Timestamps ---
            $table->timestamps();

            // --- Mendefinisikan Foreign Key Constraint ---
            $table->foreign('terima_gudang_id')->references('id')->on('th_slsgtrcv')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('td_slsgtrcv_tables');
    }
};
