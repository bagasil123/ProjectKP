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
        Schema::create('th_slsgtrcv', function (Blueprint $table) {
            // --- Kolom Kunci & Identitas ---
            $table->id(); // Kunci utama standar Laravel (setara dengan Rcv_Auto)
            $table->string('Rcv_number', 50)->unique(); // Nomor unik penerimaan, misal: RCV-20250801-001

            // --- Kunci Asing & Referensi ---
            $table->unsignedBigInteger('ref_trx_auto')->nullable(); // Kunci asing ke th_slstgt.Trx_Auto
            $table->unsignedBigInteger('user_id')->nullable(); // Kunci asing ke tabel users

            // --- Informasi Gudang & Tanggal ---
            $table->date('Rcv_Date'); // Tanggal penerimaan
            $table->string('Rcv_WareCode', 100)->nullable(); // Gudang yang menerima barang
            $table->string('Rcv_From', 100)->nullable(); // Gudang asal pengirim barang

            // --- Status & Catatan ---
            $table->char('rcv_posting', 1)->default('F'); // Status: 'F' = Draft, 'T' = Posted
            $table->text('Rcv_Note')->nullable(); // Catatan tambahan

            // --- Timestamps ---
            $table->timestamps(); // Otomatis membuat created_at dan updated_at

            // --- Mendefinisikan Foreign Key Constraints ---
            // $table->foreign('ref_trx_auto')->references('Trx_Auto')->on('th_slstgt')->onDelete('set null');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('th_slsgtrcv_tables');
    }
};
