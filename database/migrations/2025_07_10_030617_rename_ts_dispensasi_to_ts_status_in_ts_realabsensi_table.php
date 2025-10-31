<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            // Mengubah nama kolom
            $table->renameColumn('TS_DISPENSASI', 'TS_STATUS');
        });
    }

    public function down(): void
    {
        Schema::table('ts_realabsensi', function (Blueprint $table) {
            // Logika untuk mengembalikan jika migrasi di-rollback
            $table->renameColumn('TS_STATUS', 'TS_DISPENSASI');
        });
    }
};