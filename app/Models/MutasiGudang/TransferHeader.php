<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\Warehouse;
use App\Models\MutasiGudang\GudangOrder;
use App\Models\MutasiGudang\TerimaGudangHeader;

class TransferHeader extends Model
{
    use HasFactory;

    protected $table = 'th_slsgt';
    protected $primaryKey = 'Trx_Auto'; 
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'trx_number',
        'Trx_Date',
        'Trx_WareCode',
        'Trx_RcvNo',
        'Trx_Note',
        'trx_posting',      
        'Trx_UserID',          
        'ref_pur_auto',     
        'bruto_from_permintaan',
        'diskon_from_permintaan',
        'pajak_from_permintaan',
        'netto_from_permintaan',
    ];

    protected $casts = [
        'Trx_Date' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(TransferDetail::class, 'Trx_Auto', 'Trx_Auto');
    }
    
    public function gudangPengirim()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_WareCode', 'WARE_Auto');
    }

    public function gudangPenerima()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_RcvNo', 'WARE_Auto');
    }
    
    public function permintaanGudang()
    {
        return $this->belongsTo(GudangOrder::class, 'ref_pur_auto', 'Pur_Auto');
    }

    public function penerimaan()
    {
        return $this->hasOne(TerimaGudangHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }
}