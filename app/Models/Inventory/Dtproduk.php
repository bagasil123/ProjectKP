<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MutasiGudang\Warehouse;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\Kelompokproduk;  
use App\Models\Inventory\Satuanproduk;

class Dtproduk extends Model
{
    use HasFactory;

    protected $table = 'dataproduk_tabel';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'supplier_id',
        'qty',
        'harga_beli',
        'harga_jual',
        'WARE_Auto',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function incrementStock($quantity)
    {
        $this->qty += $quantity;
        $this->save();
        return $this;
    }

    public function decrementStock($quantity)
    {
        $this->qty = max(0, $this->qty - $quantity);
        $this->save();
        return $this;
    }

    public function updatePurchasePrice($price)
    {
        $this->harga_beli = $price;
        $this->save();
        return $this;
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'WARE_Auto', 'WARE_Auto');
    }

    public function kategori()
    {
        return $this->belongsTo(Kelompokproduk::class, 'product_category', 'id');
    } 
    
    public function satuan()
    {
        return $this->belongsTo(Satuanproduk::class, 'product_uom', 'id');
    }
}
