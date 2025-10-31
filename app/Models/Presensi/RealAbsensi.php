<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealAbsensi extends Model
{
    use HasFactory;

    protected $table = 'ts_realabsensi';
    protected $primaryKey = 'TS_AUTO';
    public $timestamps = false; // Karena menggunakan kolom tanggal custom

    protected $fillable = [
        'TS_EMP',
        'TS_NAME',
        'TS_CODE',
        'TS_NIK',
        'TS_TANGGAL',
        'TS_JAMIN',
        'TS_JAMOUT',
        'TS_FOTO',
        'TS_LATITUDE',
        'TS_LONGITUDE',
        'TS_FILE_PENDUKUNG',
        'TS_STATUS',
        'TS_NOTE',
        'TS_ENTRYDATE',
        'TS_ENTRYUSER',
        'TS_UPDATEDATE',    
        'TS_UPDATEUSER',
        'TS_RECORD',
        'TS_ACTIVE',
    ];

    /**
     * Relasi ke model Karyawan
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'TS_EMP', 'emp_Auto');
    }
}