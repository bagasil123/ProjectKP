<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\Dtproduk;

class TransferDetail extends Model
{
    use HasFactory;

    protected $table = 'td_slsgt';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'Trx_Auto',
        'trx_number',
        'Trx_ProdCode',
        'trx_prodname',
        'trx_uom',
        'Trx_QtyTrx',
        'trx_cogs',
        'trx_discount',
        'trx_taxes',
        'trx_nettprice',
    ];

    public function header()
    {
        return $this->belongsTo(TransferHeader::class, 'Trx_Auto', 'Trx_Auto');
    }

    public function produk()
    {
        return $this->hasOne(Dtproduk::class, 'kode_produk', 'Trx_ProdCode');
    }

        public function getNamaProdukAttribute()
    {
        return $this->produk->nama_produk ?? $this->trx_prodname ?? $this->Trx_ProdCode;
    }

    public function getSatuanAmanAttribute()
    {
        return $this->trx_uom ?? $this->produk->satuan ?? 'PCS';
    }
}