<?php

namespace App\Models\SPModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * This should match the table name created by your migration file.
     * @var string
     */
    protected $table = 'daftarpelanggan';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode',
        'anggota',
        'alamat',
        'telp',
        'email',
        'cara_bayar',
        'lama_bayar',
        'potongan',
        'nominal_plafon',
        'status',
        'tanggal'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal' => 'date',
    ];
}
