<?php

namespace App\Models\Akuntansi;

use App\Models\keamanan\Member; // Pastikan ini ada jika Anda menggunakan relasi user
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\Warehouse; // Pastikan ini ada jika Anda menggunakan relasi gudang


class AccHdjurnal extends Model
{
    use HasFactory;

    protected $table = 'acc_hd_jurnal'; // Pastikan nama tabel sudah benar

    protected $fillable = [
        'no_jurnal',
        'tanggal_buat',
        'tanggal_edit',
        'lokasi_nama',
        'referensi',
        'catatan',
        'user_id',
        'nominal',
        'tipe_jurnal',
    ];

    // ... (relasi dan method lainnya)
    protected $casts = [
        'tanggal_buat' => 'datetime', // atau 'date' jika hanya tanggal tanpa waktu
        'tanggal_edit' => 'datetime',
        'nominal' => 'float', // Casting nominal juga baik
        // 'created_at' => 'datetime', // Otomatis jika menggunakan $timestamps = true
        // 'updated_at' => 'datetime', // Otomatis jika menggunakan $timestamps = true
    ];
    public function user()
    {
        return $this->belongsTo(Member::class, 'user_id', 'Mem_Auto');

    }

    public function details()
    {
        return $this->hasMany(AccDtjurnal::class, 'acc_hd_jurnal_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'Trx_WareCode', 'WARE_Auto');
    }
}
