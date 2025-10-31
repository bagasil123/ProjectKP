<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom emp_Auto menjadi BIGINT UNSIGNED AUTO_INCREMENT
        DB::statement('ALTER TABLE m_employee MODIFY emp_Auto BIGINT UNSIGNED AUTO_INCREMENT');

        Schema::table('m_employee', function (Blueprint $table) {
            // Pastikan kolom yang menjadi foreign key tidak null atau sesuai
            $table->string('emp_DivCode', 20)->nullable()->change();
            $table->string('EMP_SUBDIVCODE', 20)->nullable()->change();
            $table->string('emp_PosCode', 20)->nullable()->change();

            // Tambahkan foreign key constraints
            $table->foreign('emp_DivCode')
                ->references('Div_Code')
                ->on('ts_div')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('EMP_SUBDIVCODE')
                ->references('Div_Code')
                ->on('ts_subdiv')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('emp_PosCode')
                ->references('Pos_Code')
                ->on('ts_position')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('m_employee', function (Blueprint $table) {
            $table->dropForeign(['emp_DivCode']);
            $table->dropForeign(['EMP_SUBDIVCODE']);
            $table->dropForeign(['emp_PosCode']);
        });

        // Rollback perubahan tipe emp_Auto jika perlu
        DB::statement('ALTER TABLE m_employee MODIFY emp_Auto INT UNSIGNED AUTO_INCREMENT');
    }
};
