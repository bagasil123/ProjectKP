<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_members', function (Blueprint $table) {
            $table->increments('Mem_Auto');
            $table->string('Mem_ID', 10)->unique();
            $table->string('Mem_UserName', 50);
            $table->text('mem_password')->nullable();
            $table->enum('Mem_ActiveYN', ['Y', 'N'])->default('Y');
            $table->enum('mem_veriterima', ['F', 'T'])->default('F');
            $table->enum('mem_veriretur', ['F', 'T'])->default('F');
            $table->string('Mem_UpdateID', 10)->nullable();
            $table->timestamp('Mem_LastUpdate')->useCurrent();
            $table->date('Mem_StartPeriod')->nullable();
            $table->date('Mem_EndPeriod')->nullable();
            $table->string('Mem_Menu', 100)->nullable();
            $table->text('Mem_Note')->nullable();
            $table->decimal('mem_count', 10, 0)->default(0);
            $table->string('Mem_rECORD', 1)->nullable();

            // Tambahkan foreign key role_id
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_members');
    }
};
