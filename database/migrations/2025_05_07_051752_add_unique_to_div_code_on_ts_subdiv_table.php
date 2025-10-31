<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ts_subdiv', function (Blueprint $table) {
            $table->unique('Div_Code'); // Menambahkan UNIQUE constraint
        });
    }

    public function down(): void
    {
        Schema::table('ts_subdiv', function (Blueprint $table) {
            $table->dropUnique(['Div_Code']); // Menghapus UNIQUE constraint
        });
    }
};

