<?php

namespace App\Models\Retur;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory\Dtproduk;
use App\Models\SPModels\Pelanggan;
use App\Models\Inventory\SatuanProduk;

class TdTrxSalesRtr extends Model
{
    protected $table = 'td_trxsalesrtr';
    protected $primaryKey = 'trx_number_dtl';
    public $incrementing  = true;
    public $keyType       = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Trx_SupCode',
        'trx_number',
        'Trx_date',
        'Trx_ProdCode',
        'trx_prodname',
        'trx_uom',
        'trx_curr',
        'Trx_QtyTrx',
        'Trx_QtyReject',
        'Trx_QtyBonus',
        'Trx_QtyBayar',
        'Trx_GrossPrice',
        'Trx_NettPrice',
        'Trx_Discount',
        'Trx_Taxes',
        'trx_cogs',
        'trx_rev',
        'trx_posting',
        'Trx_Note',
        'Trx_UpdateID',
        'Trx_LastUpdate'
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(ThTrxSalesRtr::class, 'trx_number', 'trx_number');
    }

    public function product()
    {
        return $this->belongsTo(Dtproduk::class, 'Trx_ProdCode', 'kode_produk');
    }

    public function customer()
    {
        return $this->belongsTo(Pelanggan::class, 'Pro_SupCode', 'kode');
    }

    public function uom()
    {
        return $this->belongsTo(SatuanProduk::class, 'trx_uom', 'UOM_Auto');
    }
}
