<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmenuTabel extends Migration
{
    public function up()
    {
        Schema::create('submenu_tabel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('setmenu')->onDelete('cascade');
            $table->string('nama_submenu', 100);
            $table->integer('urut')->default(0);
            $table->string('tautan', 255);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('submenu_tabel');
    }
}