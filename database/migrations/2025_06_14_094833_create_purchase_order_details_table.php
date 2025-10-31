<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->foreignId('po_id')->constrained('purchase_orders', 'po_id')->onDelete('cascade');
            
            // Tambahkan onDelete('cascade') untuk product_id
            $table->foreignId('product_id')
                ->constrained('dataproduk_tabel')
                ->onDelete('cascade');
            
            $table->foreignId('uom_id')->constrained('m_uom', 'UOM_Auto');
            $table->integer('qty');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
