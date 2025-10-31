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
        Schema::create('ts_realabsensi', function (Blueprint $table) {
            $table->id('TS_AUTO');
            
            // Relasi ke tabel m_employee
            $table->foreignId('TS_EMP')->constrained('m_employee', 'emp_Auto')->onDelete('cascade');
            
            $table->string('TS_NAME', 100)->nullable();
            $table->string('TS_CODE', 10)->nullable();
            $table->date('TS_TANGGAL')->nullable();
            $table->time('TS_JAMIN')->nullable();
            $table->time('TS_JAMOUT')->nullable();
            $table->string('TS_DISPENSASI', 250)->nullable();
            $table->string('TS_NOTE', 250)->nullable();
            $table->dateTime('TS_ENTRYDATE')->nullable();
            $table->string('TS_ENTRYUSER', 8)->nullable();
            $table->dateTime('TS_UPDATEDATE')->nullable();
            $table->string('TS_UPDATEUSER', 8)->nullable(); // Mengganti nama TS_DATEUSER menjadi lebih jelas
            $table->string('TS_RECORD', 1)->nullable()->comment('Flag data sudah diproses/direkam');
            $table->string('TS_ACTIVE', 1)->nullable()->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ts_realabsensi');
    }
};
