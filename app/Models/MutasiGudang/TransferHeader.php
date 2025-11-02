<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\Warehouse;
use App\Models\MutasiGudang\GudangOrder;

class TransferHeader extends Model
{
    use HasFactory;

    // Sesuaikan dengan nama tabel header transfer Anda
    protected $table = 'th_slsgt';
    
    // Sesuai dengan view Anda (data-id="{{ $transfer->Trx_Auto }}")
    protected $primaryKey = 'Trx_Auto'; 
    public $timestamps = false;
    public $incrementing = true; // Asumsi Trx_Auto adalah auto-increment

    // Kolom-kolom dari view Anda
    protected $fillable = [
        'trx_number',
        'Trx_Date',
        'Trx_WareCode',     // Gudang Asal (Nama)
        'Trx_RcvNo',        // Gudang Tujuan (Nama)
        'Trx_Note',
        'trx_posting',      // 'F' (Draft) atau 'T' (Posted)
        'Trx_Emp',          // User
        'ref_pur_auto',     // ID Permintaan (Pur_Auto)
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
        // Sesuaikan 'id' jika PK detail Anda beda
        return $this->hasMany(TransferDetail::class, 'Trx_Auto', 'Trx_Auto');
    }
    
    // (PERBAIKAN) Relasi ke Gudang Asal (berdasarkan NAMA)
    public function gudangPengirim()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_WareCode', 'WARE_Name');
    }

    // (PERBAIKAN) Relasi ke Gudang Tujuan (berdasarkan NAMA)
    public function gudangPenerima()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_RcvNo', 'WARE_Name');
    }
    
    // (BARU) Relasi ke Permintaan Gudang (Gudang Order)
    public function permintaanGudang()
    {
        // Asumsi 'ref_pur_auto' adalah kolom FK untuk 'Pur_Auto' di 'th_gudangorder'
        return $this->belongsTo(GudangOrder::class, 'ref_pur_auto', 'Pur_Auto');
    }
}