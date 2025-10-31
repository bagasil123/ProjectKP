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
        Schema::create('m_shift', function (Blueprint $table) {
            $table->id();
            $table->string('shift_code', 10)->unique();
            $table->string('shift_name', 50);
            $table->time('jam_in');
            $table->time('jam_out');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_shift');
    }
};
