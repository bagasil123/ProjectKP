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
        Schema::create('acc_subclass', function (Blueprint $table) {
            $table->id('cls_subid');
            $table->unsignedBigInteger('cls_id');
            $table->string('cls_ina');
            $table->timestamps();

            $table->foreign('cls_id')->references('cls_id')->on('acc_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_subclass');
    }
};
