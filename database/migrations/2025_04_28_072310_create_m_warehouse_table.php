<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_warehouse', function (Blueprint $table) {
            $table->increments('WARE_Auto');
            $table->string('WARE_Name', 50)->nullable();
            $table->string('WARE_Address', 300)->nullable();
            $table->string('WARE_Phone', 15)->nullable();
            $table->string('WARE_Fax', 15)->nullable();
            $table->string('WARE_Email', 50)->nullable();
            $table->string('WARE_Web', 50)->nullable();
            $table->string('ware_note1', 50)->nullable();
            $table->string('ware_note2', 50)->nullable();
            $table->dateTime('WARE_EntryDate')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_warehouse');
    }
};
