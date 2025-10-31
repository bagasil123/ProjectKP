<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submenu_id')->nullable()->constrained('submenu_tabel')->onDelete('set null');
            $table->string('judul');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->foreignId('kategori_berita_id')->nullable()->constrained('kategori_berita')->onDelete('set null');
            $table->foreignId('kategori_album_id')->nullable()->constrained('kategori_album')->onDelete('set null');
            $table->boolean('status')->default(true);
            $table->boolean('halaman_depan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_contents');
    }
};