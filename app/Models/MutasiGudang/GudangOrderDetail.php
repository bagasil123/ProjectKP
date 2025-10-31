<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GudangOrderDetail extends Model
{
    use HasFactory;


    protected $table = 'td_gudangorderdetail';
    protected $primaryKey = 'Pur_Det_Auto'; // Sesuaikan jika berbeda
    public $timestamps = false;

    protected $fillable = [
        'Pur_Auto', // Foreign key ke header
        'Pur_ProdCode',
        'pur_prodname',
        'Pur_UOM',
        'Pur_Qty',
        'Pur_GrossPrice',
        'Pur_Discount',
        'Pur_Taxes',
        'Pur_NettPrice',
    ];

}
