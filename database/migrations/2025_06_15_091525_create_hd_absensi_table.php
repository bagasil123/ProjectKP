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
        Schema::create('hd_absensi', function (Blueprint $table) {
            $table->id('auto');
            $table->date('pergajia')->nullable()->comment('Periode Gaji Awal');
            $table->date('pergajib')->nullable()->comment('Periode Gaji Akhir');
            $table->date('perota')->nullable()->comment('Periode Lembur Awal');
            $table->date('perotb')->nullable()->comment('Periode Lembur Akhir');
            $table->string('statuse', 10)->nullable();
            $table->string('kategori', 2)->nullable();
            $table->string('flag', 1)->default('F');
            
            // Relasi ke tabel m_employee
            $table->foreignId('emp_auto')->nullable()->constrained('m_employee', 'emp_Auto')->onDelete('set null');

            $table->string('Kode', 30);
            $table->string('Nama', 30)->nullable();
            $table->string('Departemen', 50)->nullable();
            
            // Kolom agregat untuk rekapitulasi
            $table->decimal('Total_Hadir', 16, 0)->default(0);
            $table->decimal('DayOff', 16, 0)->default(0);
            $table->decimal('B_Hari', 16, 0)->default(0)->comment('Lembur Hari Biasa (total hari)');
            $table->decimal('B_JamLembur', 16, 0)->default(0)->comment('Lembur Hari Biasa (total jam)');
            $table->decimal('B_J1', 16, 0)->default(0)->comment('Rate Lembur Biasa 1');
            $table->decimal('B_J2', 16, 0)->default(0)->comment('Rate Lembur Biasa 2');
            $table->decimal('B_J3', 16, 0)->default(0)->comment('Rate Lembur Biasa 3');
            $table->decimal('B_Kupon', 16, 0)->default(0);
            $table->decimal('L_Hari', 16, 0)->default(0)->comment('Lembur Hari Libur (total hari)');
            $table->decimal('L_JamLembur', 16, 0)->default(0)->comment('Lembur Hari Libur (total jam)');
            $table->decimal('L_J1', 16, 0)->default(0)->comment('Rate Lembur Libur 1');
            $table->decimal('L_J2', 16, 0)->default(0)->comment('Rate Lembur Libur 2');
            $table->decimal('L_J3', 16, 0)->default(0)->comment('Rate Lembur Libur 3');
            $table->decimal('L_Kupon', 16, 0)->default(0);
            $table->decimal('Cuti', 16, 0)->default(0);
            $table->decimal('Cuti_Dispensasi', 16, 0)->default(0);
            $table->decimal('Sakit', 16, 0)->default(0);
            $table->decimal('Izin', 16, 0)->default(0);
            $table->decimal('D_C', 16, 0)->default(0)->comment('Dispensasi/Cuti Khusus?');
            $table->decimal('Alpa', 16, 0)->default(0);
            $table->decimal('Shift_2', 16, 0)->default(0);
            $table->decimal('Shift_3', 16, 0)->default(0);

            $table->text('keterangan')->nullable();
            $table->string('entryuser', 10)->nullable();
            $table->timestamp('entrydate')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hd_absensi');
    }
};
