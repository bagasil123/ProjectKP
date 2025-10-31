<?php

namespace App\Models\Presensi;

use App\Models\keamanan\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Employee extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'm_employee';
    protected $primaryKey = 'emp_Auto';
    public $timestamps = false;


    protected $fillable = [
        'emp_Code',
        'emp_NID',
        'emp_Name',
        'emp_password',
        'emp_ActiveYN',
        'emp_Address',
        'emp_CityCode',
        'emp_ProvinceCode',
        'emp_DivCode',
        'EMP_SUBDIVCODE',
        'emp_PosCode',
        'emp_ZipCode',
        'emp_Phone1',
        'emp_Phone2',
        'emp_hp1',
        'emp_hp2',
        'emp_Address2',
        'emp_CityCode2',
        'emp_ProvinceCode2',
        'emp_ZipCode2',
        'emp_Phone3',
        'emp_Phone4',
        'emp_hp3',
        'emp_hp4',
        'emp_Email',
        'emp_Email2',
        'emp_Web',
        'emp_Sex',
        'emp_Marital',
        'emp_Religion',
        'emp_PlaceBorn',
        'emp_DateBorn',
        'emp_Enroll',
        'emp_startcontract',
        'emp_Expired',
        'emp_permanent',
        'emp_quit',
        'emp_reason',
        'emp_office',
        'emp_ptkp',
        'emp_blood',
        'EMP_SHIF',
        'EMP_PAJAK',
        'EMP_status',
        'emp_bayar',
        'emp_BANK',
        'emp_NOREK',
        'emp_PEMILIK',
        'emp_NPWP',
        'emp_education',
        'EMP_JAMSOSTEK',
        'emp_datejamsostek',
        'emp_ktp',
        'emp_no_ktp',
        'EMP_PICT',
        'emp_ENTRYID',
        'emp_FirstEntry',
        'emp_UpdateID',
        'emp_LastUpdate',    
    ];

    public function Divisi()
    {
        return $this->belongsTo(Divisi::class,  'emp_DivCode', 'div_auto');
    }

    public function SubDivisi()
    {
        return $this->belongsTo(SubDivisi::class,  'EMP_SUBDIVCODE', 'div_auto');
    }

    public function Posisi()
    {
        return $this->belongsTo(Posisi::class,  'emp_PosCode', 'pos_auto');
    }
     public function member()
    {
        return $this->hasOne(Member::class, 'Mem_ID', 'emp_Code');
    }
    
}
