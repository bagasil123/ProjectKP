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

    /**
     * (PERBAIKAN BESAR DI SINI)
     * Kita ubah dari belongsTo menjadi hasOne.
     * hasOne akan mengambil HANYA SATU baris produk yang cocok,
     * yang menyelesaikan masalah 'kode_produk' yang tidak unik.
     */
    public function produk()
    {
        // Parameter:
        // 1. Model tujuan: Dtproduk::class
        // 2. Foreign Key (di tabel tujuan, dataproduk_tabel): 'kode_produk'
        // 3. Local Key (di tabel ini, td_slsgt): 'Trx_ProdCode'
        return $this->hasOne(Dtproduk::class, 'kode_produk', 'Trx_ProdCode');
    }
}