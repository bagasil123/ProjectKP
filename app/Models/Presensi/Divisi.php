<?php

namespace App\Models\Presensi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;
    
    protected $table = 'ts_div';
    protected $primaryKey = 'div_auto';
    public $timestamps = false;


    protected $fillable = [
        'Div_Code',
        'Div_Name',
        'DIV_NIK',
        'DIV_SHIFTYN',
        'DIV_BIAYA',
        'Div_EntryID',
        'Div_Entrydate',
        'Div_UserID',
        'Div_LastUpdate',
    ];

    public function SubDivisi() 
    {
        return $this->hasMany(SubDivisi::class, 'div_divcode', 'div_auto');
    }

    public function Employee()
    {
        return $this->hasMany(Employee::class, 'emp_DivCode', 'div_auto');
    }
}
