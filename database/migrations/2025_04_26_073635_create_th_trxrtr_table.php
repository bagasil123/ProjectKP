<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('th_trxrtr', function (Blueprint $table) {
            $table->id('Trx_Auto');
            $table->string('Trx_SupCode', 20)->nullable();
            $table->string('Trx_WareCode', 20)->nullable();
            $table->string('trx_jurnal', 50)->nullable();
            $table->string('trx_sourcenum', 50)->nullable();
            $table->string('trx_number', 50)->nullable()->unique();
            $table->float('Trx_Discount', 5, 2)->default(0.00);
            $table->string('Trx_FakturNo', 30)->nullable();
            $table->date('Trx_Date')->nullable();
            $table->date('Trx_FakturDate')->nullable();
            $table->date('Trx_DueDate')->nullable();
            $table->string('trx_curr', 10)->nullable();
            $table->decimal('Trx_GrossPrice', 18, 2)->default(0.00);
            $table->decimal('Trx_NettPrice', 18, 2)->default(0.00);
            $table->decimal('Trx_Taxes', 18, 2)->default(0.00);
            $table->decimal('Trx_TotDiscount', 18, 2)->default(0.00);
            $table->string('trx_status', 10)->nullable();
            $table->text('Trx_Note')->nullable();
            $table->decimal('trx_payment', 18, 2)->default(0.00);
            $table->char('trx_sales', 1)->default('F');
            $table->char('trx_clear', 1)->default('F');
            $table->char('trx_posting', 1)->default('F');
            $table->integer('Trx_Print')->default(0);
            $table->string('Trx_BON', 30)->nullable();
            $table->integer('trx_rev')->default(0);
            $table->string('Trx_MerchandiserID', 20)->nullable();
            $table->string('Trx_UserID', 10)->nullable();
            $table->dateTime('Trx_LastUpdate')->nullable();
            $table->string('trx_veriuserid', 10)->nullable();
            $table->dateTime('trx_veridate')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('th_trxrtr');
    }
};
