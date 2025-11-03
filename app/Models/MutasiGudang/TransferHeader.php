<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\Warehouse;
use App\Models\MutasiGudang\GudangOrder;
// (Tambahkan use statement ini di atas)
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
        'Trx_WareCode',     // Menyimpan ID (WARE_Auto)
        'Trx_RcvNo',        // Menyimpan ID (WARE_Auto)
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

    // Relasi ke Detail
    public function details()
    {
        return $this->hasMany(TransferDetail::class, 'Trx_Auto', 'Trx_Auto');
    }
    
    // Relasi ke Gudang Asal (berdasarkan ID)
    public function gudangPengirim()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_WareCode', 'WARE_Auto');
    }

    // Relasi ke Gudang Tujuan (berdasarkan ID)
    public function gudangPenerima()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_RcvNo', 'WARE_Auto');
    }
    
    // Relasi ke Permintaan Gudang
    public function permintaanGudang()
    {
        return $this->belongsTo(GudangOrder::class, 'ref_pur_auto', 'Pur_Auto');
    }

    /**
     * (PERBAIKAN ADA DI SINI)
     * Relasi ke header penerimaan (untuk cek stok menggantung)
     * Ini akan memperbaiki error tombol
     */
    public function penerimaan()
    {
        // Parameter:
        // 1. Model tujuan: TerimaGudangHeader::class
        // 2. Foreign Key (di tabel tujuan, th_slsgtrcv): 'ref_trx_auto'
        // 3. Local Key (di tabel ini, th_slsgt): 'Trx_Auto'
        return $this->hasOne(TerimaGudangHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }
}