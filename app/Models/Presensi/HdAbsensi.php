<?php

namespace App\Models\Presensi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HdAbsensi extends Model
{
    use HasFactory;

    protected $table = 'hd_absensi';
    protected $primaryKey = 'auto';
    public $timestamps = false;

    protected $fillable = [
        'pergajia', 
        'pergajib', 
        'perota', 
        'perotb', 
        'statuse', 
        'kategori',
        'flag', 
        'emp_auto', 
        'Kode', 
        'Nama', 
        'Departemen', 
        'Total_Hadir', 
        'DayOff',
        'B_Hari', 
        'B_JamLembur', 
        'B_J1', 
        'B_J2', 
        'B_J3', 
        'B_Kupon',
        'L_Hari', 
        'L_JamLembur', 
        'L_J1', 
        'L_J2', 
        'L_J3', 
        'L_Kupon',
        'Cuti', 
        'Cuti_Dispensasi', 
        'Sakit', 
        'Izin', 
        'D_C', 
        'Alpa',
        'Shift_2', 
        'Shift_3', 
        'keterangan', 
        'entryuser', 
        'entrydate',
    ];

    /**
     * Relasi ke model Karyawan
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_auto', 'emp_Auto');
    }
}
