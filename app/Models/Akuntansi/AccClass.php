<?php

namespace App\Models\Akuntansi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccClass extends Model
{
    use HasFactory;

    protected $table = 'acc_class';
    protected $primaryKey = 'cls_id';

    protected $fillable = ['cls_ina'];

    public function subclasses()
    {
        return $this->hasMany(AccSubclass::class, 'cls_id', 'cls_id');
    }

    public function kiras()
    {
        return $this->hasMany(AccKira::class, 'cls_id', 'cls_id');
    }
}
