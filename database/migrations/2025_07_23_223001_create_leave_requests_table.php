<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('m_employee', 'emp_Auto')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type'); // Izin, Sakit, Cuti
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
};
