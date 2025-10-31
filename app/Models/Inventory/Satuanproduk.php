<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanProduk extends Model
{
    use HasFactory;

    protected $table = 'm_uom';

    protected $primaryKey = 'UOM_Auto';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;
    const CREATED_AT = 'UOM_Entrydate';
    const UPDATED_AT = 'UOM_LastUpdate';

    protected $fillable = [
        'UOM_Code',
        'UOM_Amount',
        'UOM_EntryID',
        'UOM_UpdateID',
    ];

    /**
     * Relasi ke purchase order detail jika ingin ditambahkan.
     */
    public function purchaseOrderDetails()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'satuan_id', 'UOM_Auto');
    }
}
