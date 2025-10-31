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
    public function up()
    {
        Schema::create('th_gudangorder', function (Blueprint $table) {
            $table->bigIncrements('Pur_Auto');
            $table->string('Pur_SupCode', 30)->nullable();
            $table->string('pur_ordernumber', 50)->nullable()->unique(); // UNIQUE KEY
            $table->string('pur_warehouse', 20)->nullable();
            $table->string('pur_destination', 20)->nullable();
            $table->string('pur_emp', 20)->nullable();
            $table->date('Pur_Date')->nullable();
            $table->float('Pur_Discount', 5, 2)->default(0.00);
            $table->decimal('Pur_GrossPrice', 18, 2)->default(0.00);
            $table->decimal('Pur_NettPrice', 18, 2)->default(0.00);
            $table->decimal('Pur_Taxes', 18, 2)->default(0.00);
            $table->decimal('Pur_TotDiscount', 18, 2)->default(0.00);
            $table->string('Pur_Group', 20)->nullable();
            $table->string('Pur_Note', 300)->nullable();
            $table->char('Pur_Cancel', 1)->default('F');
            $table->string('pur_status', 10)->nullable();
            $table->char('Pur_FLAG', 1)->default('F');
            $table->integer('Pur_Print')->default(0);
            $table->integer('pur_rev')->default(0);
            $table->string('Pur_UpdateID', 10)->nullable();
            $table->dateTime('Pur_LastUpdate')->nullable();

            // Catatan: Laravel biasanya menggunakan timestamps() yang membuat created_at dan updated_at.
            // Di sini kita mengikuti struktur yang Anda berikan.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('th_gudangorder');
    }
};
