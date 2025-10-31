<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetmenuTabel extends Migration
{
    public function up()
    {
        Schema::create('setmenu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu', 100);
            $table->integer('urutan')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('setmenu');
    }
}
