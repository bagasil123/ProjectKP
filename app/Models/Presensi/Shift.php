<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'm_shift';

    protected $fillable = [
        'shift_code',
        'shift_name',
        'jam_in',
        'jam_out',
    ];
}
