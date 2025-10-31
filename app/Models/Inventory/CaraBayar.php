<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaraBayar extends Model
{
    protected $table = 'cara_bayar_tabel';
    protected $fillable = ['nama'];

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'cara_bayar_id');
    }
}
