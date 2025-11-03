<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TerimaGudangHeader extends Model
{

    protected $table = 'th_slsgtrcv';
    protected $primaryKey = 'id';

    protected $fillable = [
        'Rcv_number',
        'Rcv_Date',
        'Rcv_WareCode',
        'Rcv_From',
        'Rcv_Note',
        'user_id',
        'rcv_posting',
        'ref_trx_auto',
    ];

    public function transferGudang()
    {
    return $this->belongsTo(TransferHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }

    public function details()
    {
        return $this->hasMany(TerimaGudangDetail::class, 'terima_gudang_id', 'id');
    }

    public function transferHeader()
    {
        return $this->belongsTo(TransferHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }
}
