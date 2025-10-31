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
        Schema::create('dt_jadwal', function (Blueprint $table) {
            $table->id('tmp_auto');
            $table->string('tmp_periode', 10)->nullable()->index();
            
            // Relasi ke tabel m_employee
            $table->foreignId('TMP_emp')->constrained('m_employee', 'emp_Auto')->onDelete('cascade');

            $table->string('TMP_CODE', 30);
            $table->string('TMP_NAME', 30)->default('');
            $table->string('DEPARTEMEN', 30)->default('');
            $table->string('posisi', 30)->default('');
            $table->string('emp_tgl', 2)->default('');
            $table->string('shift_code', 10)->default('');
            $table->time('jam_in')->default('00:00:00');
            $table->time('jam_out')->default('00:00:00');
            $table->time('jam_ina')->default('00:00:00')->comment('Jam Masuk Aktual');
            $table->time('jam_outa')->default('00:00:00')->comment('Jam Keluar Aktual');
            $table->string('TMP_FLAG', 1)->default('F')->comment('Flag pemrosesan data');
            $table->string('tmp_user', 10)->nullable();
            $table->timestamp('tmp_date')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dt_jadwal');
    }
};
