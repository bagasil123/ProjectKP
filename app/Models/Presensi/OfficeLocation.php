<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    use HasFactory;
    protected $table = 'm_officeloc';
    protected $fillable = ['name', 'latitude', 'longitude'];
}