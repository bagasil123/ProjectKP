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
        Schema::table('dt_jadwal', function (Blueprint $table) {
            // Menambahkan kolom latitude dan longitude setelah kolom jam_outa
            // Menggunakan tipe data decimal untuk presisi koordinat GPS
            // Dibuat nullable agar tidak error pada data jadwal yang sudah ada
            $table->decimal('latitude', 10, 8)->nullable()->after('jam_outa');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dt_jadwal', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
