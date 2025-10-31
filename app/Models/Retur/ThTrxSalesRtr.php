<?php

namespace App\Models\Retur;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\keamanan\Member;
use App\Models\SPModels\Pelanggan;
use App\Models\MutasiGudang\Warehouse;

class ThTrxSalesRtr extends Model
{
    protected $table = 'th_trxsalesrtr';
    protected $primaryKey = 'Trx_Auto';
    public $timestamps = false;

    protected $fillable = [
        'Trx_SupCode',
        'Trx_WareCode',
        'trx_jurnal',
        'trx_sourcenum',
        'trx_number',
        'Trx_Discount',
        'Trx_FakturNo',
        'Trx_Date',
        'Trx_FakturDate',
        'Trx_DueDate',
        'trx_curr',
        'Trx_GrossPrice',
        'Trx_NettPrice',
        'Trx_Taxes',
        'Trx_TotDiscount',
        'trx_status',
        'Trx_Note',
        'trx_payment',
        'trx_clear',
        'trx_posting',
        'Trx_Print',
        'Trx_BON',
        'trx_rev',
        'Trx_MerchandiserID',
        'Trx_UserID',
        'Trx_LastUpdate'
    ];

    protected $casts = [
        'Trx_Date'       => 'date',
        'Trx_LastUpdate' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TdTrxSalesRtr::class, 'trx_number', 'trx_number');
    }

    public function user()
    {
        return $this->belongsTo(Member::class, 'Trx_UserID', 'Mem_Auto');
    }

    public function customer()
    {
        return $this->belongsTo(Pelanggan::class, 'Trx_SupCode', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_WareCode', 'WARE_Auto');
    }
}
