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
        Schema::create('ts_subdiv', function (Blueprint $table) {
            $table->increments('div_auto');
            $table->unsignedInteger('div_divcode')->nullable();
            $table->string('Div_Code', 20)->nullable();
            $table->string('Div_Name', 50)->nullable();
            $table->string('DIV_NIK', 20)->nullable();
            $table->string('Div_EntryID', 10)->nullable();
            $table->dateTime('Div_Entrydate');
            $table->string('Div_UserID', 10)->nullable();
            $table->dateTime('Div_LastUpdate')->nullable();

            // Foreign key constraint
            $table->foreign('div_divcode')
                ->references('div_auto')
                ->on('ts_div')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_subdiv');
    }
};
