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
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            // Kolom untuk menyimpan nama file foto bukti (selfie)
            $table->string('TS_FOTO', 255)->nullable()->after('TS_JAMOUT');
            
            // Kolom untuk menyimpan data lokasi
            $table->decimal('TS_LATITUDE', 10, 8)->nullable()->after('TS_FOTO');
            $table->decimal('TS_LONGITUDE', 11, 8)->nullable()->after('TS_LATITUDE');

            // Kolom untuk menyimpan nama file dokumen pendukung (misal: surat sakit)
            $table->string('TS_FILE_PENDUKUNG', 255)->nullable()->after('TS_LONGITUDE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            $table->dropColumn(['TS_FOTO', 'TS_LATITUDE', 'TS_LONGITUDE', 'TS_FILE_PENDUKUNG']);
        });
    }
};
