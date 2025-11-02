<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDetail extends Model
{
    use HasFactory;

    // Sesuaikan dengan nama tabel detail transfer Anda
    protected $table = 'td_slsgt';
    
    // Sesuai dengan view Anda (data-id="{{ $detail->id }}")
    protected $primaryKey = 'id'; // Asumsi 'id'
    public $timestamps = false;
    public $incrementing = true;

    // Kolom-kolom dari view Anda
    protected $fillable = [
        'Trx_Auto',         // Foreign Key
        'trx_number',
        'Trx_ProdCode',
        'trx_prodname',
        'trx_uom',
        'Trx_QtyTrx',
        'trx_cogs',         // Harga
        'trx_discount',
        'trx_taxes',
        'trx_nettprice',    // Subtotal
    ];

    // Relasi kembali ke Header
    public function header()
    {
        return $this->belongsTo(TransferHeader::class, 'Trx_Auto', 'Trx_Auto');
    }
}