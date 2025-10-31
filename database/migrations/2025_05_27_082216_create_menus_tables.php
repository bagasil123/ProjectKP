<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // Nama menu atau submenu
            $table->string('slug')->unique();           // Unik, bisa digunakan sebagai route name / identifier
            $table->string('url')->nullable();          // URL untuk routing
            $table->string('icon')->nullable();         // Ikon font-awesome atau lainnya (opsional)
            $table->unsignedInteger('order')->default(0); // Urutan menu saat ditampilkan
            $table->foreignId('parent_id')              // Untuk submenu
                  ->nullable()
                  ->constrained('menus')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
