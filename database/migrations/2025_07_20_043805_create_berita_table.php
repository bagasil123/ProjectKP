<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul_berita');
            $table->string('slug')->unique();
            $table->text('isi_berita');
            $table->string('gambar_berita')->nullable();
            $table->foreignId('kategori_id')->constrained('kategori_berita')->onDelete('cascade');
            $table->string('penulis')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};