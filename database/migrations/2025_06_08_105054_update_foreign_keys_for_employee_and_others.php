<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Perbaikan ini akan mengubah foreign key dari rujukan kode bisnis (string)
     * menjadi rujukan primary key (integer) untuk integritas dan performa.
     */
    public function up(): void
    {
        Schema::table('m_employee', function (Blueprint $table) {
            $table->dropForeign(['emp_DivCode']);
            $table->dropForeign(['EMP_SUBDIVCODE']);
            $table->dropForeign(['emp_PosCode']);
        });

        Schema::table('ts_div', function (Blueprint $table) {
            $table->dropUnique(['Div_Code']); // <-- KODE DITAMBAHKAN
            $table->string('Div_Code', 20)->nullable()->change();
        });
        Schema::table('ts_subdiv', function (Blueprint $table) {
            $table->dropUnique(['Div_Code']); // <-- KODE DITAMBAHKAN
            $table->string('Div_Code', 20)->nullable()->change();
        });
        Schema::table('ts_position', function (Blueprint $table) {
            $table->dropUnique(['Pos_Code']); // <-- KODE DITAMBAHKAN
            $table->string('Pos_Code', 60)->nullable()->change();
        });

        Schema::table('m_employee', function (Blueprint $table) {
            $table->unsignedInteger('emp_DivCode')->nullable()->change();
            $table->unsignedInteger('EMP_SUBDIVCODE')->nullable()->change();
            $table->unsignedInteger('emp_PosCode')->nullable()->change();

            // 4. FK dari m_employee -> ts_div
            $table->foreign('emp_DivCode')
                  ->references('div_auto')->on('ts_div')
                  ->onDelete('set null')->onUpdate('cascade');

            // 2. FK dari m_employee -> ts_subdiv
            $table->foreign('EMP_SUBDIVCODE')
                  ->references('div_auto')->on('ts_subdiv')
                  ->onDelete('set null')->onUpdate('cascade');
                  
            // 3. FK dari m_employee -> ts_position
            $table->foreign('emp_PosCode')
                  ->references('pos_auto')->on('ts_position')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Mengembalikan skema ke kondisi sebelum perbaikan.
     */
    public function down(): void
    {
        Schema::table('m_employee', function (Blueprint $table) {
            $table->dropForeign(['emp_DivCode']);
            $table->dropForeign(['EMP_SUBDIVCODE']);
            $table->dropForeign(['emp_PosCode']);
            
            // Kembalikan tipe kolom ke string
            $table->string('emp_DivCode', 20)->nullable()->change();
            $table->string('EMP_SUBDIVCODE', 20)->nullable()->change();
            $table->string('emp_PosCode', 20)->nullable()->change();
        });

        Schema::table('m_employee', function (Blueprint $table) {
            $table->foreign('emp_DivCode')
                  ->references('Div_Code')->on('ts_div')
                  ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('EMP_SUBDIVCODE')
                  ->references('Div_Code')->on('ts_subdiv')
                  ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('emp_PosCode')
                  ->references('Pos_Code')->on('ts_position')
                  ->onDelete('set null')->onUpdate('cascade');
        });

        Schema::table('ts_div', function (Blueprint $table) {
            $table->unique('Div_Code'); // <-- KODE DITAMBAHKAN
        });
        Schema::table('ts_subdiv', function (Blueprint $table) {
            $table->unique('Div_Code'); // <-- KODE DITAMBAHKAN
        });
        Schema::table('ts_position', function (Blueprint $table) {
            $table->unique('Pos_Code'); // <-- KODE DITAMBAHKAN
        });
    }
};