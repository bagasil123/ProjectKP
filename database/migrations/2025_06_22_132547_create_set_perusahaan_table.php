<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSetPerusahaanTable extends Migration
{
    public function up()
    {
        Schema::create('set_perusahaan', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('address');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('email_account')->nullable();
            $table->string('email_password')->nullable();
            $table->string('email_host')->nullable();
            $table->string('smtp_port')->nullable();
            $table->string('tagline')->nullable();
            $table->text('map_location')->nullable();
            $table->string('logo')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('set_perusahaan');
    }
}