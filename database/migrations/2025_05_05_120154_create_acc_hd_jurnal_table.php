<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_hd_jurnal', function (Blueprint $table) {
            $table->id();
            $table->string('no_jurnal')->unique()->comment('Nomor Jurnal (YYMM-NNNNN-JU)');
            $table->date('tanggal_buat')->comment('Tanggal Transaksi/Pembuatan');
            $table->dateTime('tanggal_edit')->comment('Tanggal Terakhir Diedit');
            $table->string('lokasi_nama')->nullable()->comment('Nama Lokasi (Placeholder)'); // Ganti ke lokasi_id jika sudah ada tabelnya
            $table->string('referensi')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->constrained('users')->comment('User yang menginput/edit terakhir');
            $table->decimal('nominal', 15, 2)->default(0)->comment('Total Debet/Kredit Jurnal');
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_hd_jurnal');
    }
};
