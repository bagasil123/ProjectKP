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
        Schema::create('m_employee', function (Blueprint $table) {
            $table->increments('emp_Auto');
            $table->string('emp_Code', 20)->unique()->nullable();
            $table->string('emp_NID', 30)->nullable();
            $table->string('emp_Name', 50)->nullable();
            $table->string('emp_ActiveYN', 1)->nullable();
            $table->string('emp_Address', 200)->nullable();
            $table->string('emp_CityCode', 20)->nullable();
            $table->string('emp_ProvinceCode', 20)->nullable();
            $table->string('emp_DivCode', 20)->nullable();
            $table->string('EMP_SUBDIVCODE', 20)->nullable();
            $table->string('emp_PosCode', 20)->nullable();
            $table->string('emp_ZipCode', 5)->nullable();
            $table->string('emp_Phone1', 15)->nullable();
            $table->string('emp_Phone2', 15)->nullable();
            $table->string('emp_hp1', 15)->nullable();
            $table->string('emp_hp2', 15)->nullable();
            $table->string('emp_Address2', 200)->nullable();
            $table->string('emp_CityCode2', 20)->nullable();
            $table->string('emp_ProvinceCode2', 20)->nullable();
            $table->string('emp_ZipCode2', 5)->nullable();
            $table->string('emp_Phone3', 15)->nullable();
            $table->string('emp_Phone4', 15)->nullable();
            $table->string('emp_hp3', 15)->nullable();
            $table->string('emp_hp4', 15)->nullable();
            $table->string('emp_Email', 50)->nullable();
            $table->string('emp_Email2', 50)->nullable();
            $table->string('emp_Web', 50)->nullable();
            $table->string('emp_Sex', 2)->nullable();
            $table->string('emp_Marital', 2)->nullable();
            $table->string('emp_Religion', 30)->nullable();
            $table->string('emp_PlaceBorn', 30)->nullable();
            $table->date('emp_DateBorn')->nullable();
            $table->date('emp_Enroll')->nullable();
            $table->date('emp_startcontract')->nullable();
            $table->date('emp_Expired')->nullable();
            $table->date('emp_permanent')->nullable();
            $table->date('emp_quit')->nullable();
            $table->string('emp_reason', 3)->nullable();
            $table->string('emp_office', 10)->nullable();
            $table->string('emp_ptkp', 10)->nullable();
            $table->string('emp_blood', 2)->nullable();
            $table->string('EMP_SHIF', 10)->nullable();
            $table->string('EMP_PAJAK', 2)->nullable();
            $table->string('EMP_status', 2)->nullable();
            $table->string('emp_bayar', 2)->nullable();
            $table->string('emp_BANK', 10)->nullable();
            $table->string('emp_NOREK', 20)->nullable();
            $table->string('emp_PEMILIK', 50)->nullable();
            $table->string('emp_NPWP', 50)->nullable();
            $table->string('emp_education', 3)->nullable();
            $table->string('EMP_JAMSOSTEK', 50)->nullable();
            $table->date('emp_datejamsostek')->nullable();
            $table->string('emp_ktp', 3)->nullable();
            $table->string('emp_no_ktp', 30)->nullable();
            $table->binary('EMP_PICT')->nullable();
            $table->string('emp_ENTRYID', 10)->nullable();
            $table->timestamp('emp_FirstEntry')->nullable();
            $table->string('emp_UpdateID', 10)->nullable();
            $table->dateTime('emp_LastUpdate')->nullable();
        });

        // Modifikasi setelah tabel dibuat
        DB::statement('ALTER TABLE m_employee MODIFY EMP_PICT MEDIUMBLOB NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_employee');
    }
};
