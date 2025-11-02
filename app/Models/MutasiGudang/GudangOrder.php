<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\MutasiGudang\GudangOrderDetail;
use Illuminate\Database\Eloquent\Casts\Attribute; // Penting untuk Laravel 9+


class GudangOrder extends Model
{
    use HasFactory;

    protected $table = 'th_gudangorder';
    protected $primaryKey = 'Pur_Auto';
    public $timestamps = false;

    protected $fillable = [
    'pur_ordernumber',
    'Pur_Date',
    'pur_status',
    'pur_emp',
    'pur_warehouse',
    'pur_destination',
    'Pur_SupCode',
    'Pur_Note',
    ];


    protected $casts = [
        'Pur_Date' => 'date',
    ];

    public function gudangPengirim()
    {
        return $this->belongsTo(Warehouse::class, 'pur_warehouse', 'WARE_Auto');
    }

    public function gudangPenerima()
    {
        return $this->belongsTo(Warehouse::class, 'pur_destination', 'WARE_Auto');
    }


    public function details()
    { 
        return $this->hasMany(GudangOrderDetail::class, 'Pur_Auto', 'Pur_Auto');
    }

    protected function totalBruto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum(function ($detail) { 
                return $detail->Pur_Qty * $detail->Pur_GrossPrice;
            }),
        );
    }

    protected function totalDiscount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('Pur_Discount'),
        );
    } 
    protected function totalTaxes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('Pur_Taxes'),
        );
    }

    protected function grandTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('Pur_NettPrice'),
        );
    }

}
