<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferDetail extends Model
{
    use HasFactory;

    protected $table = 'td_slsgt';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'trx_number',
        'Trx_ProdCode',
        'trx_prodname',
        'trx_uom',
        'Trx_QtyTrx',
        'Trx_QtyRcv',
        'Trx_QtyReject',
        'trx_cogs',
        'trx_posting',
        'trx_rcvposting',
        'Trx_UpdateID',
        'trx_discount',  // <-- PASTIKAN ADA
        'trx_taxes',     // <-- PASTIKAN ADA
        'trx_nettprice', // <-- PASTIKAN ADA
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(TransferHeader::class, 'trx_number', 'trx_number');
    }
}
