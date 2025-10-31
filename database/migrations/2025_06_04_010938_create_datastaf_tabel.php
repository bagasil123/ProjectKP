<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('datastaf_tabel', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('jabatan');
            $table->string('profile_image')->nullable();
            $table->text('description');
            $table->text('education');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Untuk kolom deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('datastaf_tabel');
    }
};
