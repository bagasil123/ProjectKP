<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'contact_person',
        'telp',
        'email',
        'cara_bayar_id',
        'lama_bayar',
        'potongan',
        'tanggal'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function caraBayar(): BelongsTo
    {
        return $this->belongsTo(CaraBayar::class, 'cara_bayar_id');
    }
    public function penerimaan(): HasMany
    {
        return $this->hasMany(Penerimaan::class, 'supplier_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    public function getUniqueIdentifierAttribute()
    {
        return $this->kode_supplier . ' - ' . $this->nama_supplier . ' (' . $this->telp . ')';
    }

    public function toSelect2Format()
    {
        return [
            'id' => $this->id,
            'text' => $this->getUniqueIdentifierAttribute()
        ];
    }
}
