<?php

namespace App\Models\Presensi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDivisi extends Model
{
    use HasFactory;

    protected $table = 'ts_subdiv';
    protected $primaryKey = 'div_auto';
    public $timestamps = false;


    protected $fillable = [
        'div_divcode',
        'Div_Code',
        'Div_Name',
        'DIV_NIK',
        'Div_EntryID',
        'Div_Entrydate',
        'Div_UserID',
        'Div_LastUpdate',
    ];

    public function Divisi()
    {
        return $this->belongsTo(Divisi::class,  'div_divcode', 'div_auto');
    }

    public function Employee()
    {
        return $this->hasMany(Employee::class, 'EMP_SUBDIVCODE', 'div_auto');
    }
}
