<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ts_div', function (Blueprint $table) {
            $table->increments('div_auto'); // AUTO_INCREMENT, primary key
            $table->string('Div_Code', 20)->nullable();
            $table->string('Div_Name', 50)->nullable();
            $table->string('DIV_NIK', 20)->nullable();
            $table->char('DIV_SHIFTYN', 1)->default('T');
            $table->char('DIV_BIAYA', 1)->nullable();
            $table->string('Div_EntryID', 10)->nullable();
            $table->dateTime('Div_Entrydate');
            $table->string('Div_UserID', 10)->nullable();
            $table->dateTime('Div_LastUpdate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_div');
    }
};
