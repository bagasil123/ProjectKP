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
        Schema::table('m_employee', function (Blueprint $table) {
            // Menambahkan kolom password setelah kolom emp_Name
            $table->string('emp_password', 255)->nullable()->after('emp_Name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_employee', function (Blueprint $table) {
            $table->dropColumn('emp_password');
        });
    }
};
