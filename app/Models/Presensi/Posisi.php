<?php

namespace App\Models\Presensi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posisi extends Model
{
    use HasFactory;

    protected $table = 'ts_position';
    protected $primaryKey = 'pos_auto';
    public $timestamps = false;


    protected $fillable = [
        'Pos_Code',
        'Pos_Name',
        'Pos_UserID',
        'Pos_LastUpdate',
    ];

    public function Employee()
    {
        return $this->hasMany(Employee::class, 'emp_PosCode', 'pos_auto');
    }
}
