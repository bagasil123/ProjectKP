<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('setmenu', function (Blueprint $table) {
            $table->string('route_name', 100)->nullable()->after('nama_menu');
            $table->string('tautan')->nullable()->after('route_name');
        });
    }

    public function down()
    {
        Schema::table('setmenu', function (Blueprint $table) {
            $table->dropColumn(['route_name', 'tautan']);
        });
    }
};