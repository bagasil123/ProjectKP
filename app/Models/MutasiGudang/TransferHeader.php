<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

// Pastikan model GudangOrder di-import
use App\Models\MutasiGudang\GudangOrder;

class TransferHeader extends Model
{
    protected $table = 'th_slsgt';
    protected $primaryKey = 'Trx_Auto';

    // ... properti $fillable Anda ...
    protected $fillable = [
        'trx_number',
        'Trx_WareCode',
        'Trx_RcvNo',
        'Trx_Date',
        'Trx_type',
        'Trx_Note',
        'Trx_UserID',
        'trx_posting',
        'ref_pur_auto', // Pastikan ini ada di $fillable
    ];


    // Relasi ke detail transfer (yang sudah ada)
    // app/Models/MutasiGudang/TransferHeader.php
    public function details()
    {
        // Parameter:
        // 1. Model Detail: TransferDetail::class
        // 2. Foreign Key di tabel detail (td_slsgt): 'trx_number'
        // 3. Local Key di tabel header ini (th_slstgt): 'trx_number'
        return $this->hasMany(TransferDetail::class, 'trx_number', 'trx_number');
    }

    // ==========================================================
    // RELASI BARU: Membuat "jembatan" ke Permintaan Gudang asli
    // ==========================================================
    public function permintaanGudang()
    {
        // Menghubungkan kolom 'ref_pur_auto' di tabel ini
        // dengan kolom 'Pur_Auto' di tabel 'th_gudangorder'
        return $this->belongsTo(GudangOrder::class, 'ref_pur_auto', 'Pur_Auto');
    }

    public function penerimaan()
    {
        // Parameter:
        // 1. Model yang berelasi: TerimaGudangHeader::class
        // 2. Foreign key di tabel th_slsgtrcv (tabel penerimaan): 'ref_trx_auto'
        // 3. Local key (primary key) di tabel th_slstgt (tabel transfer ini): 'Trx_Auto'
        return $this->hasOne(TerimaGudangHeader::class, 'ref_trx_auto', 'Trx_Auto');
    }

    // ==========================================================
    // ACCESSOR BARU: Mengambil total finansial melalui jembatan di atas
    // ==========================================================

    /**
     * Mengambil Total Bruto dari Permintaan Gudang yang terhubung.
     * Dipanggil di view dengan: $transfer->bruto_from_permintaan
     */
    protected function brutoFromPermintaan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->permintaanGudang->total_bruto ?? 0,
        );
    }

    /**
     * Mengambil Total Diskon dari Permintaan Gudang yang terhubung.
     * Dipanggil di view dengan: $transfer->diskon_from_permintaan
     */
    protected function diskonFromPermintaan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->permintaanGudang->total_discount ?? 0,
        );
    }

    /**
     * Mengambil Total Pajak dari Permintaan Gudang yang terhubung.
     * Dipanggil di view dengan: $transfer->pajak_from_permintaan
     */
    protected function pajakFromPermintaan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->permintaanGudang->total_taxes ?? 0,
        );
    }

    /**
     * Mengambil Grand Total (Netto) dari Permintaan Gudang yang terhubung.
     * Dipanggil di view dengan: $transfer->netto_from_permintaan
     */
    protected function nettoFromPermintaan(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->permintaanGudang->grand_total ?? 0,
        );
    }
}
