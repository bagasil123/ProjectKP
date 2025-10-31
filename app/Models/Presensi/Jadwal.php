<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dt_jadwal';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'tmp_auto';

    /**
     * Laravel's created_at and updated_at are not used.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tmp_periode',
        'TMP_emp',
        'TMP_CODE',
        'TMP_NAME',
        'DEPARTEMEN',
        'posisi',
        'emp_tgl',
        'shift_code',
        'jam_in',
        'jam_out',
        'jam_ina',
        'jam_outa',
        'TMP_FLAG',
        'tmp_user',
        'tmp_date',
        'latitude',
        'longitude',

    ];

    /**
     * Definisi relasi: Setiap baris jadwal dimiliki oleh satu karyawan.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'TMP_emp', 'emp_Auto');
    }
}
