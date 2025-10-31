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
        Schema::create('acc_kira', function (Blueprint $table) {
            $table->id();
            $table->string('cls_kiraid');
            $table->unsignedBigInteger('cls_id');
            $table->unsignedBigInteger('cls_subid');
            $table->string('cls_ina');
            $table->enum('status', ['umum', 'cash/bank'])->default('umum');
            $table->enum('d_k', ['debet', 'kredit']);
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('cls_id')->references('cls_id')->on('acc_class');
            $table->foreign('cls_subid')->references('cls_subid')->on('acc_subclass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acc_kira');
    }
};
