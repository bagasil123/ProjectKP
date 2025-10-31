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
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            // Menambahkan kolom TS_NIK setelah kolom TS_CODE
            // Kolom ini dibuat nullable untuk menghindari error pada data yang sudah ada.
            $table->string('TS_NIK')->nullable()->after('TS_CODE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            // Menghapus kolom TS_NIK jika migrasi di-rollback
            $table->dropColumn('TS_NIK');
        });
    }
};
