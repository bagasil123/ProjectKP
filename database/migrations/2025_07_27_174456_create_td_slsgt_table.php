<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('td_slsgt', function (Blueprint $table) {
            $table->id(); // Kunci utama untuk setiap baris detail
            $table->string('trx_number', 50)->index(); // Foreign key, di-index untuk performa
            $table->string('Trx_ProdCode', 30);

            // Kolom kuantitas
            $table->decimal('Trx_QtyTrx', 18, 2)->default(0.00);
            $table->decimal('Trx_QtyRcv', 18, 2)->default(0.00);
            $table->decimal('Trx_QtyReject', 18, 2)->default(0.00);

            // Kolom lainnya
            $table->decimal('trx_discount', 15, 2)->default(0);
            $table->decimal('trx_taxes', 15, 2)->default(0);
            $table->decimal('trx_nettprice', 15, 2)->default(0);
            $table->string('trx_prodname', 100)->nullable();
            $table->string('trx_uom', 10)->nullable();
            $table->decimal('trx_cogs', 18, 2)->nullable();
            $table->char('trx_posting', 1)->default('F');
            $table->char('trx_rcvposting', 1)->default('F');
            $table->string('Trx_UpdateID', 10)->nullable();
            $table->timestamps(0);

            // Mendefinisikan Foreign Key Constraint
            // $table->foreign('trx_number')->references('trx_number')->on('th_slsgt')->onDelete('cascade');
            // Catatan: Uncomment baris di atas untuk integritas data yang lebih kuat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('td_slsgt');
    }
};
