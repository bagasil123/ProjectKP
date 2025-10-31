<?php

namespace App\Models\Akuntansi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccKira extends Model
{
    protected $table = 'acc_kira';

    use HasFactory;

    protected $fillable = [
        'cls_kiraid',
        'cls_id',
        'cls_subid',
        'cls_ina',
        'status',
        'd_k',
        'tanggal'
    ];

    public function accClass()
    {
        return $this->belongsTo(AccClass::class, 'cls_id', 'cls_id');
    }

    public function accSubclass()
    {
        return $this->belongsTo(AccSubclass::class, 'cls_subid', 'cls_subid');
    }
}
