<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class KelompokProduk extends Model
{
    protected $table = 'kelompokproduk_tabel';
    
    protected $fillable = [
        'nama_kelompok'
    ];

    public function produks()
    {
        return $this->hasMany(Dtproduk::class);
    }
}