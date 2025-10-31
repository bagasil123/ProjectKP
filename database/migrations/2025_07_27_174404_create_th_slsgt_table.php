<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('th_slsgt', function (Blueprint $table) {
            // Kolom utama
            $table->id('Trx_Auto'); // AUTO_INCREMENT PRIMARY KEY
            $table->string('trx_number', 50)->unique();
            $table->string('Trx_WareCode', 20)->nullable()->comment('Gudang Asal');
            $table->string('Trx_RcvNo', 30)->nullable()->comment('Gudang Tujuan / Cabang Penerima');
            $table->date('Trx_Date')->nullable();

            // Kolom referensi ke th_gudangorder
            $table->unsignedBigInteger('ref_pur_auto')->nullable();

            // Kolom status dan tipe
            $table->string('trx_type', 1)->default('T')->comment('T untuk Transfer');
            $table->char('trx_posting', 1)->default('F');
            $table->char('trx_rcvposting', 1)->default('F');

            // Kolom lain-lain
            $table->text('Trx_Note')->nullable();
            $table->string('Trx_UserID', 10)->nullable();
            $table->string('Trx_RcvUser', 10)->nullable();
            $table->date('Trx_RcvDate')->nullable();
            $table->timestamps(0); // created_at dan updated_at

            // Opsional: foreign key constraint jika Pur_Auto adalah unsigned INT
            $table->foreign('ref_pur_auto')->references('Pur_Auto')->on('th_gudangorder')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('th_slsgt');
    }
};
