<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_order_details', function (Blueprint $table) {
            $table->id();
            // PERBAIKAN: constrained() sekarang merujuk ke 'customer_orders'
            $table->foreignId('customer_order_id')->constrained('customer_orders')->onDelete('cascade');
            // Pastikan tabel 'products' ada sebelum migrasi ini dijalankan
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

    public function down()
    {
        Schema::dropIfExists('customer_order_details');
    }
};
