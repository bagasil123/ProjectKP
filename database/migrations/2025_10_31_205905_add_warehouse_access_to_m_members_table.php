<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseAccessToMMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_members', function (Blueprint $table) {
            // Menambahkan kolom baru untuk menyimpan ID gudang dalam format JSON
            // Kolom ini bisa null jika user tidak punya akses gudang
            $table->json('warehouse_access')->nullable()->after('role_id'); 
            // ganti 'role_id' jika Anda ingin meletakkannya setelah kolom lain
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_members', function (Blueprint $table) {
            $table->dropColumn('warehouse_access');
        });
    }
}