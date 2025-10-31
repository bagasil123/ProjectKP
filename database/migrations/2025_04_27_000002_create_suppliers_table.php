<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_supplier', 20)->unique();
            $table->string('nama_supplier', 255);
            $table->text('alamat');
            $table->string('contact_person', 100);
            $table->string('telp', 20);
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('cara_bayar_id')->nullable();
            $table->integer('lama_bayar')->default(0);
            $table->decimal('potongan', 5, 2)->default(0.00);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
