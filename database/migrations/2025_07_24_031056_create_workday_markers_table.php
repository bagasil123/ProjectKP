<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workday_markers', function (Blueprint $table) {
            $table->id();
            // Menggunakan foreignId untuk relasi ke tabel m_employee
            $table->foreignId('employee_id')->constrained('m_employee', 'emp_Auto');
            $table->date('date')->unique(); // 'date' dibuat unik untuk memastikan hanya ada 1 data per hari
            $table->time('clock_in_time');
            $table->boolean('is_workday_flag')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workday_markers');
    }
};
