<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiburNasional extends Model
{
    use HasFactory;

    protected $table = 'm_libur_nasional';

    protected $fillable = [
        'tanggal',
        'keterangan',
    ];
}
