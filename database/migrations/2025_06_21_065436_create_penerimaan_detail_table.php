<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for penerimaan detail table.
     */
    public function up()
    {
        Schema::create('penerimaan_detail', function (Blueprint $table) {
            $table->id('detail_id');

            // Foreign key to penerimaan header
            $table->foreignId('penerimaan_id')
                    ->constrained('penerimaan', 'penerimaan_id')
                    ->onDelete('cascade');

            // Foreign key to product
            $table->foreignId('product_id')
                  ->constrained('dataproduk_tabel')
                  ->onDelete('restrict');

            // Quantity with 2 decimal places
            $table->decimal('qty', 12, 2);

            // Foreign key to unit of measure
            $table->foreignId('uom_id')
                  ->constrained('m_uom', 'UOM_Auto')
                  ->onDelete('restrict');

            // Purchase price with 2 decimal places
            $table->decimal('harga_beli', 15, 2);

            // Discount percentage (0-100) with 2 decimal places
            $table->decimal('diskon_persen', 5, 2)
                  ->default(0);

            // Tax percentage (0-100) with 2 decimal places
            $table->decimal('pajak_persen', 5, 2)
                  ->default(0);

            // Calculated subtotal
            $table->decimal('subtotal', 15, 2);

            // Optional notes
            $table->text('catatan')
                  ->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('penerimaan_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('penerimaan_detail');
    }
};
