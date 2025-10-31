<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_details';
    protected $primaryKey = 'detail_id';
    public $incrementing = true;
    
    protected $fillable = [
        'po_id',
        'product_id',
        'uom_id',
        'qty',
        'unit_price',
        'tax_percent',
        'discount_percent',
        'note'
    ];

    public function product()
    {
        return $this->belongsTo(Dtproduk::class, 'product_id');
    }

    public function uom()
    {
        return $this->belongsTo(SatuanProduk::class, 'uom_id', 'UOM_Auto');
    }
}