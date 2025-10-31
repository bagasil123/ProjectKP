<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerimaGudangDetail extends Model
{
    use HasFactory;

    protected $table = 'td_slsgtrcv';

    protected $fillable = [
        'terima_gudang_id',
        'Rcv_ProdCode',
        'Rcv_prodname',
        'Rcv_uom',
        'Rcv_Qty_Sent',
        'Rcv_Qty_Received',
        'Rcv_Qty_Rejected',
        'Rcv_cogs',
        'Rcv_subtotal',
    ];

    // Relasi kembali ke header
    public function header()
    {
        return $this->belongsTo(TerimaGudangHeader::class, 'terima_gudang_id', 'id');
    }
}
