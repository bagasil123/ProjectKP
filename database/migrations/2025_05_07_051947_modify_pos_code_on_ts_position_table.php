<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ts_position', function (Blueprint $table) {
            // Ubah panjang kolom dan tambahkan UNIQUE
            $table->string('Pos_Code', 20)->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ts_position', function (Blueprint $table) {
            // Kembalikan ke panjang sebelumnya dan hapus UNIQUE
            $table->dropUnique(['Pos_Code']);
            $table->string('Pos_Code', 60)->nullable()->change();
        });
    }
};

