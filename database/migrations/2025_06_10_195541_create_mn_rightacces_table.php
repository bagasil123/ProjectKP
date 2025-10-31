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
        // HANYA LAKUKAN INI DI LINGKUNGAN DEVELOPMENT ATAU JIKA AMAN KEHILANGAN DATA!
        // Ini akan menghapus tabel mn_rightacces jika sudah ada dan membuatnya ulang.
        Schema::dropIfExists('mn_rightacces');

        Schema::create('mn_rightacces', function (Blueprint $table) {
            $table->string('AC_USER', 10); // Foreign key ke m_members.Mem_ID
            // AC_MAINMENU sekarang adalah ID dari tabel `role_menu`
            $table->unsignedBigInteger('AC_MAINMENU'); // Foreign key ke role_menu.id

            $table->enum('AC_DE', ['F', 'T'])->default('F'); // Delete
            $table->enum('AC_ED', ['F', 'T'])->default('F'); // Edit
            $table->enum('AC_AD', ['F', 'T'])->default('F'); // Add
            // Kolom AC_EX (Export) Dihapus

            $table->string('AC_USERID', 10)->nullable(); // ID yang mengupdate
            $table->timestamp('AC_LASTUPDATE')->useCurrent(); // Waktu terakhir update

            // Primary key komposit: Kombinasi AC_USER dan AC_MAINMENU (role_menu.id)
            $table->primary(['AC_USER', 'AC_MAINMENU']);

            // Foreign key ke m_members
            $table->foreign('AC_USER')->references('Mem_ID')->on('m_members')->onDelete('cascade');

            // Foreign key ke role_menu.id
            $table->foreign('AC_MAINMENU')->references('id')->on('role_menu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mn_rightacces');
    }
};