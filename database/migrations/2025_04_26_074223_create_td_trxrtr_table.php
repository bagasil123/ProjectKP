<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('td_trxrtr', function (Blueprint $table) {
            $table->id('trx_number_dtl');
            $table->string('Trx_SupCode', 20)->nullable();
            $table->string('Trx_warecode', 10)->nullable();
            $table->string('trx_sourcenumber', 50)->nullable();
            $table->string('trx_number', 50)->nullable();
            $table->date('Trx_date')->nullable();
            $table->string('Trx_ProdCode', 30)->nullable();
            $table->string('trx_prodname', 100)->nullable();
            $table->string('trx_uom', 10)->nullable();
            $table->string('trx_curr', 10)->nullable();
            $table->decimal('Trx_QtyTrx', 18, 2)->default(0.00);
            $table->decimal('Trx_QtyReject', 18, 2)->default(0.00);
            $table->decimal('Trx_QtyBonus', 18, 2)->default(0.00);
            $table->decimal('Trx_QtyBayar', 18, 2)->default(0.00);
            $table->decimal('Trx_GrossPrice', 18, 2)->default(0.00);
            $table->decimal('Trx_NettPrice', 18, 2)->default(0.00);
            $table->float('Trx_Discount', 5, 2)->default(0.00);
            $table->float('Trx_Taxes', 5, 2)->default(0.00);
            $table->decimal('trx_cogs', 18, 2)->nullable();
            $table->integer('trx_rev')->default(0);
            $table->char('trx_posting', 1)->default('F');
            $table->text('Trx_Note')->nullable();
            $table->string('Trx_UpdateID', 10)->nullable();
            $table->dateTime('Trx_LastUpdate')->nullable();

            $table->foreign('trx_number')
                ->references('trx_number')
                ->on('th_trxrtr')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('td_trxrtr');
    }
};
