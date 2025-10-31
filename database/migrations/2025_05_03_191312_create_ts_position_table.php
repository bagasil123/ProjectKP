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
        Schema::create('ts_position', function (Blueprint $table) {
            $table->increments('pos_auto'); // AUTO_INCREMENT, primary key
            $table->string('Pos_Code', 60)->nullable();
            $table->string('Pos_Name', 150)->nullable();
            $table->string('Pos_UserID', 30)->nullable();
            $table->dateTime('Pos_LastUpdate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_position');
    }
};
