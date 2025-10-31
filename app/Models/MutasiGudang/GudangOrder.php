<?php

namespace App\Models\MutasiGudang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\GudangOrderDetail;
use Illuminate\Database\Eloquent\Casts\Attribute; // Penting untuk Laravel 9+


class GudangOrder extends Model
{
    use HasFactory;

    protected $table = 'th_gudangorder';
    protected $primaryKey = 'Pur_Auto';

    /**
     * PERBAIKAN: Menonaktifkan fitur timestamps otomatis (created_at & updated_at).
     * Tambahkan baris ini. Laravel tidak akan lagi mencari atau mencoba mengisi kolom ini.
     */
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


    public function details()
    {
        return $this->hasMany(GudangOrderDetail::class, 'Pur_Auto', 'Pur_Auto');
    }

    protected function totalBruto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum(function ($detail) {
                // Kalkulasi subtotal untuk setiap item detail
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

    /**
     * ACCESSOR: Menghitung total pajak dari semua detail.
     * Dapat dipanggil di view dengan: $order->total_taxes
     */
    protected function totalTaxes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('Pur_Taxes'),
        );
    }

    /**
     * ACCESSOR: Menghitung Grand Total (Harga Bersih) dari semua detail.
     * Dapat dipanggil di view dengan: $order->grand_total
     */
    protected function grandTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('Pur_NettPrice'),
        );
    }

}
