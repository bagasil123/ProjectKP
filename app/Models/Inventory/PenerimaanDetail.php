<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanDetail extends Model
{
    protected $table = 'penerimaan_detail';
    protected $primaryKey = 'detail_id';
    
    protected $fillable = [
        'penerimaan_id',
        'product_id',
        'qty',
        'uom_id',
        'harga_beli',
        'diskon_persen',
        'pajak_persen',
        'subtotal',
        'catatan'
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'pajak_persen' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(Penerimaan::class, 'penerimaan_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Dtproduk::class, 'product_id')->withDefault();
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(SatuanProduk::class, 'uom_id', 'UOM_Auto');
    }

    public function calculateSubtotal(): float
    {
        $subtotal = $this->qty * $this->harga_beli;
        $diskon = $subtotal * ($this->diskon_persen / 100);
        $pajak = ($subtotal - $diskon) * ($this->pajak_persen / 100);
        
        return $subtotal - $diskon + $pajak;
    }
}