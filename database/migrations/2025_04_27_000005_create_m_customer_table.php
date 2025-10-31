<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMCustomerTable extends Migration
{
    public function up()
    {
        Schema::create('m_customer', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer', 20)->unique();
            $table->string('nama_customer', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('m_customer');
    }
}
