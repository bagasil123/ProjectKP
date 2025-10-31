<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';
    protected $primaryKey = 'po_id';
    
    protected $fillable = [
        'po_number',
        'supplier_id',
        'purchase_type',
        'location_id',
        'delivery_date',
        'note',
        'status'
    ];

    protected $casts = [
        'delivery_date' => 'date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'po_id');
    }
    
    public function getRouteKeyName()
    {
        return 'po_id';
    }
}