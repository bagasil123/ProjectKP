<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('penerimaan', function (Blueprint $table) {
            $table->id('penerimaan_id');
            $table->string('no_penerimaan', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('po_id')->constrained('purchase_orders', 'po_id')->onDelete('restrict');
            $table->date('tgl_terima');
            $table->string('gudang', 20);
            $table->string('faktur', 50);
            $table->date('jatuh_tempo');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('penerimaan');
    }
};