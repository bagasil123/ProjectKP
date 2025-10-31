<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acc_dt_jurnal', function (Blueprint $table) {
            $table->id();
            // Foreign key ke header jurnal
            $table->foreignId('acc_hd_jurnal_id')->constrained('acc_hd_jurnal')->onDelete('cascade');
             // Foreign key ke tabel perkiraan (acc_kira)
            $table->foreignId('acc_kira_id')->constrained('acc_kira');
            $table->decimal('debet', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps(); // Tidak wajib untuk detail, tapi bisa berguna
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acc_dt_jurnal');
    }
};
