<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TerimaGudangHeader extends Model
{
    // Sesuaikan nama tabel dan primary key dengan database Anda
    protected $table = 'th_slsgtrcv';
    protected $primaryKey = 'id'; // Ganti jika nama primary key berbeda

    protected $fillable = [
        'Rcv_number', // Asumsi nama kolom nomor penerimaan
        'Rcv_Date',   // Asumsi nama kolom tanggal
        'Rcv_WareCode', // Gudang Penerima
        'Rcv_From',     // Gudang Pengirim
        'Rcv_Note',
        'user_id',
        'rcv_posting',  // Asumsi nama kolom status posting
        'ref_trx_auto', // Kolom referensi ke transfer
    ];

    // Relasi ke detail penerimaan
    public function transferGudang()
    {
    return $this->belongsTo(TransferHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }

    public function details()
    {
        return $this->hasMany(TerimaGudangDetail::class, 'terima_gudang_id', 'id');
    }

    public function transferHeader() // <-- Nama harus sama persis dengan yang dipanggil
    {
        // Parameter:
        // 1. Model yang dituju: TransferHeader::class
        // 2. Foreign Key di tabel ini (th_slsgtrcv): 'ref_trx_auto'
        // 3. Owner Key / Primary Key di tabel tujuan (th_slstgt): 'Trx_Auto'
        return $this->belongsTo(TransferHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }
}
