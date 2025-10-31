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
        // Method up() untuk menghapus unique constraint
        Schema::table('m_employee', function (Blueprint $table) {
            $table->dropUnique(['emp_Code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Method down() untuk mengembalikan unique constraint jika di-rollback
        Schema::table('m_employee', function (Blueprint $table) {
            $table->unique('emp_Code');
        });
    }
};