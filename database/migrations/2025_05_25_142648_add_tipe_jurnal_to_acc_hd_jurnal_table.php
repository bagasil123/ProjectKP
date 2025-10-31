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
        Schema::table('acc_hd_jurnal', function (Blueprint $table) {
            // Anda bisa menggunakan ENUM jika database Anda mendukungnya dan Anda tahu tipe-tipe yang pasti
            // Contoh ENUM: $table->enum('tipe_jurnal', ['JU', 'KM', 'KK', 'PJ', 'PB'])->default('JU')->after('catatan');
            // Atau VARCHAR untuk fleksibilitas lebih
            $table->string('tipe_jurnal', 10)->default('JU')->after('catatan')->index(); // 'JU' (Jurnal Umum), 'KM' (Kas Masuk), 'KK' (Kas Keluar), dll.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acc_hd_jurnal', function (Blueprint $table) {
            $table->dropColumn('tipe_jurnal');
        });
    }
};
