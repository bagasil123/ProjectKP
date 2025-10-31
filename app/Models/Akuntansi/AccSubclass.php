<?php

namespace App\Models\Akuntansi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccSubclass extends Model
{
    use HasFactory;

    protected $table = 'acc_subclass';
    protected $primaryKey = 'cls_subid';

    protected $fillable = ['cls_id', 'cls_ina'];

    public function accClass()
    {
        return $this->belongsTo(AccClass::class, 'cls_id', 'cls_id');
    }

    public function kiras()
    {
        return $this->hasMany(AccKira::class, 'cls_subid', 'cls_subid');
    }
}
