<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('td_gudangorderdetail', function (Blueprint $table) {
            $table->bigIncrements('Pur_Det_Auto');
            $table->unsignedBigInteger('Pur_Auto');
            $table->string('Pur_ProdCode', 50);
            $table->string('pur_prodname', 255);
            $table->string('Pur_UOM', 50)->nullable();
            $table->integer('Pur_Qty');
            $table->decimal('Pur_GrossPrice', 15, 2);
            $table->foreign('Pur_Auto')
                  ->references('Pur_Auto')->on('th_gudangorder')
                  ->onDelete('cascade');
            $table->decimal('Pur_Discount', 15, 2)->default(0);
            $table->decimal('Pur_Taxes', 15, 2)->default(0);
            $table->decimal('Pur_NettPrice', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('td_gudangorderdetail');
    }
};
